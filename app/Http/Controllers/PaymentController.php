<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PosSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Check if showing deleted payments
        $showDeleted = $request->has('show_deleted') && $request->show_deleted == '1';
        
        // Super admins can see all payments, others only their hotel
        $query = Payment::query();
        
        if ($showDeleted) {
            $query->withTrashed();
        }
        
        if (!$isSuperAdmin) {
            $query->where('hotel_id', $hotelId);
        }
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
        }
        
        $query->with(['booking', 'posSale', 'receivedBy', 'hotel']);

        // Filter by payment type (booking or pos)
        if ($request->has('payment_type') && $request->payment_type) {
            if ($request->payment_type === 'booking') {
                $query->whereNotNull('booking_id');
            } elseif ($request->payment_type === 'pos') {
                $query->whereNotNull('pos_sale_id');
            }
        }

        // Filter by booking
        if ($request->has('booking_id') && $request->booking_id) {
            $query->where('booking_id', $request->booking_id);
        }

        // Filter by POS sale
        if ($request->has('pos_sale_id') && $request->pos_sale_id) {
            $query->where('pos_sale_id', $request->pos_sale_id);
        }

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('paid_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('paid_at', '<=', $request->to_date);
        }

        $payments = $query->orderBy('paid_at', 'desc')->paginate(20)->withQueryString();

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? \App\Models\Hotel::orderBy('name')->get() : collect();

        // Get bookings and POS sales for filter dropdowns (if needed)
        $bookings = collect();
        $posSales = collect();
        if ($hotelId || ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id)) {
            $filterHotelId = $isSuperAdmin && $request->has('hotel_id') && $request->hotel_id ? $request->hotel_id : $hotelId;
            $bookings = Booking::where('hotel_id', $filterHotelId)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get(['id', 'booking_reference', 'guest_name', 'created_at']);
            $posSales = PosSale::where('hotel_id', $filterHotelId)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get(['id', 'sale_reference', 'created_at']);
        }

        // Count deleted payments
        $deletedCount = 0;
        $deletedQuery = Payment::onlyTrashed();
        if (!$isSuperAdmin) {
            $deletedQuery->where('hotel_id', $hotelId);
        }
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $deletedQuery->where('hotel_id', $request->hotel_id);
        }
        $deletedCount = $deletedQuery->count();

        return view('payments.index', compact('payments', 'hotels', 'isSuperAdmin', 'bookings', 'posSales', 'showDeleted', 'deletedCount'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create(Request $request)
    {
        $hotelId = session('hotel_id');
        
        // Log access to payment creation form
        logActivity('create_form_accessed', null, "Accessed payment creation form", [
            'user_id' => auth()->id(),
            'hotel_id' => $hotelId,
        ]);
        
        $bookingId = $request->get('booking_id');
        $posSaleId = $request->get('pos_sale_id');
        
        $booking = null;
        $posSale = null;
        
        if ($bookingId) {
            $booking = Booking::where('hotel_id', $hotelId)->findOrFail($bookingId);
        }
        
        if ($posSaleId) {
            $posSale = PosSale::where('hotel_id', $hotelId)->findOrFail($posSaleId);
        }

        return view('payments.create', compact('booking', 'posSale'));
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'pos_sale_id' => 'nullable|exists:pos_sales,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer,other',
            'reference_number' => 'nullable|string|max:255',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Ensure payment is for either booking or POS sale, but not both
        $bookingId = $validated['booking_id'] ?? null;
        $posSaleId = $validated['pos_sale_id'] ?? null;
        
        if (!$bookingId && !$posSaleId) {
            return back()->withErrors(['booking_id' => 'Payment must be for either a booking or POS sale.'])->withInput();
        }

        if ($bookingId && $posSaleId) {
            return back()->withErrors(['booking_id' => 'Payment cannot be for both booking and POS sale.'])->withInput();
        }

        $hotelId = session('hotel_id');

        // Verify booking or POS sale belongs to hotel
        if ($bookingId) {
            $booking = Booking::findOrFail($bookingId);
            if (!auth()->user()->isSuperAdmin() && $booking->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this booking.');
            }

            // Do not allow payments on cancelled bookings
            if ($booking->status === 'cancelled') {
                return back()->withErrors([
                    'booking_id' => 'Cannot add a payment to a cancelled booking.',
                ])->withInput();
            }
            
            // Check if payment exceeds outstanding balance
            if ($validated['amount'] > $booking->outstanding_balance) {
                return back()->withErrors(['amount' => 'Payment amount cannot exceed outstanding balance.'])->withInput();
            }
        }

        if ($posSaleId) {
            $posSale = PosSale::findOrFail($posSaleId);
            if (!auth()->user()->isSuperAdmin() && $posSale->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this POS sale.');
            }
            
            // Check if payment exceeds outstanding balance
            if ($validated['amount'] > $posSale->outstanding_balance) {
                return back()->withErrors(['amount' => 'Payment amount cannot exceed outstanding balance.'])->withInput();
            }
        }

        $payment = null;
        
        DB::transaction(function () use ($validated, $hotelId, $bookingId, $posSaleId, &$payment) {
            // Create payment
            $payment = Payment::create([
                'hotel_id' => $hotelId,
                'booking_id' => $bookingId,
                'pos_sale_id' => $posSaleId,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'paid_at' => $validated['paid_at'],
                'received_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update payment status on booking or POS sale
            if ($bookingId) {
                $booking = Booking::findOrFail($bookingId);
                // Payment status update can be done here if needed
            }

            if ($posSaleId) {
                $posSale = PosSale::findOrFail($posSaleId);
                $posSale->updatePaymentStatus();
            }
        });
        
        // Log payment creation
        $paymentType = $bookingId ? 'Booking' : 'POS Sale';
        $reference = $bookingId ? "Booking #{$bookingId}" : "POS Sale #{$posSaleId}";
        logActivity('created', $payment, "Created payment: {$paymentType} - {$reference} - Amount: $" . number_format($payment->amount, 2), [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'booking_id' => $bookingId,
            'pos_sale_id' => $posSaleId,
        ]);

        $redirectRoute = $bookingId 
            ? route('bookings.show', $bookingId)
            : route('pos-sales.show', $posSaleId);

        return redirect($redirectRoute)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $this->authorizeHotel($payment);
        
        // Log payment viewing
        $paymentType = $payment->booking_id ? 'Booking' : 'POS Sale';
        $reference = $payment->booking_id ? "Booking #{$payment->booking_id}" : "POS Sale #{$payment->pos_sale_id}";
        logActivity('viewed', $payment, "Viewed payment: {$paymentType} - {$reference} - Amount: $" . number_format($payment->amount, 2), [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
        ]);
        
        $payment->load('booking', 'posSale', 'receivedBy');
        
        return view('payments.show', compact('payment'));
    }

    /**
     * Remove the specified payment (soft delete)
     */
    public function destroy(Payment $payment)
    {
        $this->authorizeHotel($payment);

        // Capture payment details before deletion
        $paymentDetails = [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'booking_id' => $payment->booking_id,
            'pos_sale_id' => $payment->pos_sale_id,
            'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : null,
        ];

        DB::transaction(function () use ($payment) {
            $bookingId = $payment->booking_id;
            $posSaleId = $payment->pos_sale_id;
            
            // Soft delete the payment
            $payment->delete();

            // Update payment status
            if ($bookingId) {
                $booking = Booking::find($bookingId);
                // Status update can be done here if needed
            }

            if ($posSaleId) {
                $posSale = PosSale::find($posSaleId);
                $posSale->updatePaymentStatus();
            }
        });

        // Log the deletion
        $paymentType = $paymentDetails['booking_id'] ? 'Booking' : 'POS Sale';
        $reference = $paymentDetails['booking_id'] ? "Booking #{$paymentDetails['booking_id']}" : "POS Sale #{$paymentDetails['pos_sale_id']}";
        logActivity('deleted', $payment, "Deleted payment: {$paymentType} - {$reference} - Amount: $" . number_format($paymentDetails['amount'], 2), $paymentDetails);

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Restore a soft-deleted payment
     */
    public function restore($id)
    {
        $payment = Payment::withTrashed()->findOrFail($id);
        $this->authorizeHotel($payment);

        $payment->restore();

        // Log the restoration
        $paymentType = $payment->booking_id ? 'Booking' : 'POS Sale';
        $reference = $payment->booking_id ? "Booking #{$payment->booking_id}" : "POS Sale #{$payment->pos_sale_id}";
        logActivity('restored', $payment, "Restored payment: {$paymentType} - {$reference} - Amount: $" . number_format($payment->amount, 2), [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
        ]);

        return redirect()->route('payments.index')
            ->with('success', 'Payment restored successfully.');
    }

    /**
     * Permanently delete a payment
     */
    public function forceDelete($id)
    {
        $payment = Payment::withTrashed()->findOrFail($id);
        $this->authorizeHotel($payment);

        // Capture payment details before permanent deletion
        $paymentDetails = [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'booking_id' => $payment->booking_id,
            'pos_sale_id' => $payment->pos_sale_id,
            'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : null,
        ];

        // Permanently delete the payment
        $payment->forceDelete();

        // Log the permanent deletion
        $paymentType = $paymentDetails['booking_id'] ? 'Booking' : 'POS Sale';
        $reference = $paymentDetails['booking_id'] ? "Booking #{$paymentDetails['booking_id']}" : "POS Sale #{$paymentDetails['pos_sale_id']}";
        logActivity('force_deleted', null, "Permanently deleted payment: {$paymentType} - {$reference} - Amount: $" . number_format($paymentDetails['amount'], 2), $paymentDetails);

        return redirect()->route('payments.index')
            ->with('success', 'Payment permanently deleted.');
    }

    /**
     * Ensure payment belongs to current hotel
     */
    private function authorizeHotel(Payment $payment)
    {
        // Super admins can access any payment
        if (auth()->user()->isSuperAdmin()) {
            return;
        }
        
        if ($payment->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this payment.');
        }
    }
}
