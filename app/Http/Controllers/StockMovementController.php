<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use App\Models\StockMovement;
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
        
        $query = StockMovement::where('hotel_id', $hotelId)
            ->with('product', 'creator');

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get all products for filter
        $products = Extra::where('hotel_id', $hotelId)
            ->where('stock_tracked', true)
            ->orderBy('name')
            ->get();

        return view('stock-movements.index', compact('movements', 'products'));
    }

    /**
     * Show the form for creating a new stock movement
     */
    public function create()
    {
        $hotelId = session('hotel_id');
        $products = Extra::where('hotel_id', $hotelId)
            ->where('stock_tracked', true)
            ->orderBy('name')
            ->get();

        return view('stock-movements.create', compact('products'));
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
        $product = Extra::findOrFail($validated['product_id']);

        // Ensure product belongs to hotel and stock tracking is enabled
        if ($product->hotel_id != $hotelId) {
            abort(403, 'Unauthorized access to this product.');
        }

        if (!$product->stock_tracked) {
            return back()->withErrors(['product_id' => 'Stock tracking is not enabled for this product.'])->withInput();
        }

        StockMovement::create([
            'hotel_id' => $hotelId,
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
    public function balance()
    {
        $hotelId = session('hotel_id');
        
        $products = Extra::where('hotel_id', $hotelId)
            ->where('stock_tracked', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($hotelId) {
                $product->current_stock = $product->getStockBalance($hotelId);
                $product->is_low = $product->isLowStock($hotelId);
                return $product;
            });

        return view('stock-movements.balance', compact('products'));
    }
}
