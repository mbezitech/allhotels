<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\Room;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosSaleController extends Controller
{
    /**
     * Display a listing of POS sales for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admins can see all POS sales, others only their hotel
        $query = PosSale::query();
        if (!$isSuperAdmin) {
            $query->where('hotel_id', $hotelId);
        }
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
        }
        
        $query->with(['room', 'items.extra', 'user', 'hotel']);

        // Filter by date
        if ($request->has('date') && $request->date) {
            $query->whereDate('sale_date', $request->date);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $sales = $query->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? \App\Models\Hotel::orderBy('name')->get() : collect();

        return view('pos-sales.index', compact('sales', 'hotels', 'isSuperAdmin'));
    }

    /**
     * Show the form for creating a new POS sale
     */
    public function create()
    {
        $hotelId = session('hotel_id');
        $extras = Extra::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get()
            ->map(function ($extra) use ($hotelId) {
                // Add stock balance to each extra
                $extra->current_stock = $extra->stock_tracked ? $extra->getStockBalance($hotelId) : null;
                $extra->is_low_stock = $extra->isLowStock($hotelId);
                return $extra;
            })
            ->groupBy(function ($extra) {
                if ($extra->category && is_object($extra->category)) {
                    return $extra->category->name;
                }
                return 'Uncategorized';
            });
        
        $rooms = Room::where('hotel_id', $hotelId)
            ->orderBy('room_number')
            ->get();

        return view('pos-sales.create', compact('extras', 'rooms'));
    }

    /**
     * Store a newly created POS sale
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.extra_id' => 'required|exists:extras,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $hotelId = session('hotel_id');

        // Verify room belongs to hotel if provided (unless super admin)
        if ($validated['room_id']) {
            $room = Room::findOrFail($validated['room_id']);
            if (!auth()->user()->isSuperAdmin() && $room->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this room.');
            }
        }

        // Filter out items with quantity 0, validate stock, and calculate totals
        $items = [];
        $totalAmount = 0;
        $stockErrors = [];
        
        foreach ($validated['items'] as $index => $item) {
            if (isset($item['quantity']) && $item['quantity'] > 0) {
                $extra = Extra::findOrFail($item['extra_id']);
                
                // Validate stock availability if stock tracking is enabled
                if ($extra->stock_tracked) {
                    $currentStock = $extra->getStockBalance($hotelId);
                    if ($currentStock < $item['quantity']) {
                        $stockErrors[] = "{$extra->name}: Insufficient stock. Available: {$currentStock}, Requested: {$item['quantity']}";
                        continue; // Skip this item
                    }
                }
                
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;
                $items[] = $item;
            }
        }

        if (!empty($stockErrors)) {
            return back()->withErrors(['items' => $stockErrors])->withInput();
        }

        if (empty($items)) {
            return back()->withErrors(['items' => 'Please select at least one item.'])->withInput();
        }

        $discount = $validated['discount'] ?? 0;
        $finalAmount = $totalAmount - $discount;

        DB::transaction(function () use ($validated, $hotelId, $totalAmount, $discount, $finalAmount, $items) {
            // Create POS sale
            $sale = PosSale::create([
                'hotel_id' => $hotelId,
                'room_id' => $validated['room_id'] ?? null,
                'sale_date' => $validated['sale_date'],
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'payment_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create sale items and stock movements
            foreach ($items as $item) {
                $extra = Extra::findOrFail($item['extra_id']);
                
                // Create sale item
                PosSaleItem::create([
                    'pos_sale_id' => $sale->id,
                    'extra_id' => $item['extra_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);

                // Create stock movement if stock tracking is enabled
                if ($extra->stock_tracked) {
                    StockMovement::create([
                        'hotel_id' => $hotelId,
                        'product_id' => $item['extra_id'],
                        'type' => 'out',
                        'quantity' => $item['quantity'],
                        'reference_type' => PosSale::class,
                        'reference_id' => $sale->id,
                        'notes' => 'POS Sale',
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        });

        return redirect()->route('pos-sales.index')
            ->with('success', 'POS sale created successfully.');
    }

    /**
     * Display the specified POS sale
     */
    public function show(PosSale $posSale)
    {
        $this->authorizeHotel($posSale);
        
        $posSale->load('room', 'items.extra', 'payments', 'user');
        
        return view('pos-sales.show', compact('posSale'));
    }

    /**
     * Ensure POS sale belongs to current hotel
     */
    private function authorizeHotel(PosSale $posSale)
    {
        // Super admins can access any POS sale
        if (auth()->user()->isSuperAdmin()) {
            return;
        }
        
        if ($posSale->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this POS sale.');
        }
    }
}
