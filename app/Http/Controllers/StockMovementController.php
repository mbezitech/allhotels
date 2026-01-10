<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use App\Models\StockMovement;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admins can see all stock movements, others only their hotel
        $query = StockMovement::query();
        if (!$isSuperAdmin) {
            if (!$hotelId) {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a hotel to view stock movements.');
            }
            $query->where('hotel_id', $hotelId);
        }
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
            $selectedHotelId = $request->hotel_id;
        } else {
            $selectedHotelId = $hotelId;
        }

        $query->with('product', 'creator', 'hotel');

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get all products for filter (scoped to selected hotel or all for super admin)
        $productsQuery = Extra::where('stock_tracked', true);
        if ($selectedHotelId && !$isSuperAdmin) {
            $productsQuery->where('hotel_id', $selectedHotelId);
        } elseif ($isSuperAdmin && $selectedHotelId) {
            $productsQuery->where('hotel_id', $selectedHotelId);
        }
        // If super admin and no hotel selected, show all products
        $products = $productsQuery->orderBy('name')->get();

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? Hotel::orderBy('name')->get() : collect();

        return view('stock-movements.index', compact('movements', 'products', 'hotels', 'isSuperAdmin', 'selectedHotelId'));
    }

    /**
     * Show the form for creating a new stock movement
     */
    public function create(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // For super admins, allow selecting hotel or use session hotel
        $selectedHotelId = $request->get('hotel_id', $hotelId);
        
        $productsQuery = Extra::where('stock_tracked', true);
        if ($selectedHotelId) {
            $productsQuery->where('hotel_id', $selectedHotelId);
        } elseif (!$isSuperAdmin) {
            // Regular users must have a hotel
            if (!$hotelId) {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a hotel to create a stock movement.');
            }
            $productsQuery->where('hotel_id', $hotelId);
        }
        // If super admin and no hotel selected, show all products
        $products = $productsQuery->orderBy('name')->get();
        
        // Get all hotels for super admin
        $hotels = $isSuperAdmin ? Hotel::orderBy('name')->get() : collect();

        return view('stock-movements.create', compact('products', 'hotels', 'isSuperAdmin', 'selectedHotelId'));
    }

    /**
     * Store a newly created stock movement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:extras,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $product = Extra::findOrFail($validated['product_id']);

        // For super admins, use hotel_id from request if provided, otherwise use product's hotel
        $taskHotelId = $request->get('hotel_id', $product->hotel_id);
        if (!$isSuperAdmin) {
            $taskHotelId = $hotelId;
        }

        // Ensure product belongs to hotel and stock tracking is enabled (unless super admin)
        if (!$isSuperAdmin && $product->hotel_id != $hotelId) {
            abort(403, 'Unauthorized access to this product.');
        }

        if (!$product->stock_tracked) {
            return back()->withErrors(['product_id' => 'Stock tracking is not enabled for this product.'])->withInput();
        }

        StockMovement::create([
            'hotel_id' => $taskHotelId,
            'product_id' => $validated['product_id'],
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'reference_type' => 'manual',
            'reference_id' => null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stock movement recorded successfully.');
    }

    /**
     * Display stock balance for all products
     */
    public function balance(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $selectedHotelId = $request->hotel_id;
        } else {
            $selectedHotelId = $hotelId;
        }
        
        $productsQuery = Extra::where('stock_tracked', true);
        if (!$isSuperAdmin) {
            if (!$hotelId) {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a hotel to view stock balance.');
            }
            $productsQuery->where('hotel_id', $hotelId);
        } elseif ($selectedHotelId) {
            $productsQuery->where('hotel_id', $selectedHotelId);
        }
        // If super admin and no hotel selected, show all products
        
        $products = $productsQuery->with('category', 'hotel')
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($selectedHotelId, $isSuperAdmin) {
                // Always use the product's hotel_id for stock balance calculation
                $productHotelId = $product->hotel_id;
                $product->current_stock = $product->getStockBalance($productHotelId);
                $product->is_low = $product->isLowStock($productHotelId);
                return $product;
            });

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? Hotel::orderBy('name')->get() : collect();

        return view('stock-movements.balance', compact('products', 'hotels', 'isSuperAdmin', 'selectedHotelId'));
    }
}
