<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\Room;
use App\Models\Booking;
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
        
        $query->with(['room', 'items.extra', 'user', 'hotel', 'booking']);

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by room
        if ($request->has('room_id') && $request->room_id) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by booking
        if ($request->has('booking_id') && $request->booking_id) {
            $query->where('booking_id', $request->booking_id);
        }

        // Filter by amount range
        if ($request->has('amount_from') && $request->amount_from) {
            $query->where('final_amount', '>=', $request->amount_from);
        }
        if ($request->has('amount_to') && $request->amount_to) {
            $query->where('final_amount', '<=', $request->amount_to);
        }

        // Search by sale reference
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sale_reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $sales = $query->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? \App\Models\Hotel::orderBy('name')->get() : collect();

        // Get rooms for filter dropdown
        $rooms = collect();
        $bookings = collect();
        if ($hotelId || ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id)) {
            $filterHotelId = $isSuperAdmin && $request->has('hotel_id') && $request->hotel_id ? $request->hotel_id : $hotelId;
            $rooms = Room::where('hotel_id', $filterHotelId)
                ->orderBy('room_number')
                ->get(['id', 'room_number']);
            $bookings = Booking::where('hotel_id', $filterHotelId)
                ->where('status', 'checked_in')
                ->with('room')
                ->orderBy('check_in', 'desc')
                ->limit(100)
                ->get(['id', 'booking_reference', 'guest_name', 'room_id']);
        }

        return view('pos-sales.index', compact('sales', 'hotels', 'isSuperAdmin', 'rooms', 'bookings'));
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

        // Get only checked-in bookings for guest billing (exclude confirmed and checked-out)
        $activeBookings = Booking::where('hotel_id', $hotelId)
            ->where('status', 'checked_in')
            ->with('room')
            ->orderBy('check_in', 'desc')
            ->get();

        return view('pos-sales.create', compact('extras', 'rooms', 'activeBookings'));
    }

    /**
     * Store a newly created POS sale
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'booking_id' => 'nullable|exists:bookings,id',
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

        // Verify booking belongs to hotel and is active if provided
        if (!empty($validated['booking_id'])) {
            $booking = Booking::findOrFail($validated['booking_id']);
            if (!auth()->user()->isSuperAdmin() && $booking->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this booking.');
            }
            
            // Ensure booking is checked-in (only checked-in bookings can have charges attached)
            if ($booking->status !== 'checked_in') {
                return back()->withErrors(['booking_id' => 'Can only attach charges to checked-in bookings.'])->withInput();
            }
            
            // If room_id is provided, ensure it matches booking's room
            if ($validated['room_id'] && $booking->room_id != $validated['room_id']) {
                return back()->withErrors(['room_id' => 'Selected room does not match the booking\'s room.'])->withInput();
            }
            
            // Auto-set room_id from booking if not provided
            if (!$validated['room_id'] && $booking->room_id) {
                $validated['room_id'] = $booking->room_id;
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
                'booking_id' => $validated['booking_id'] ?? null,
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
