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
        
        $query = PosSale::where('hotel_id', $hotelId)
            ->with('room', 'items.extra');

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

        return view('pos-sales.index', compact('sales'));
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
            ->groupBy(function ($extra) {
                return $extra->category ? $extra->category->name : 'Uncategorized';
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

        // Verify room belongs to hotel if provided
        if ($validated['room_id']) {
            $room = Room::findOrFail($validated['room_id']);
            if ($room->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this room.');
            }
        }

        // Filter out items with quantity 0 and calculate totals
        $items = [];
        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            if (isset($item['quantity']) && $item['quantity'] > 0) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;
                $items[] = $item;
            }
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
        
        $posSale->load('room', 'items.extra', 'payments');
        
        return view('pos-sales.show', compact('posSale'));
    }

    /**
     * Ensure POS sale belongs to current hotel
     */
    private function authorizeHotel(PosSale $posSale)
    {
        if ($posSale->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this POS sale.');
        }
    }
}
