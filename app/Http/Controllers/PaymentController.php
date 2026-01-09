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
        
        $query = Payment::where('hotel_id', $hotelId)
            ->with('booking', 'posSale', 'receivedBy');

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

        $payments = $query->orderBy('paid_at', 'desc')->paginate(20);

        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create(Request $request)
    {
        $hotelId = session('hotel_id');
        
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
            if ($booking->hotel_id != $hotelId) {
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
            if ($posSale->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this POS sale.');
            }
            
            // Check if payment exceeds outstanding balance
            if ($validated['amount'] > $posSale->outstanding_balance) {
                return back()->withErrors(['amount' => 'Payment amount cannot exceed outstanding balance.'])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $hotelId, $bookingId, $posSaleId) {
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
        
        $payment->load('booking', 'posSale', 'receivedBy');
        
        return view('payments.show', compact('payment'));
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment)
    {
        $this->authorizeHotel($payment);

        DB::transaction(function () use ($payment) {
            $bookingId = $payment->booking_id;
            $posSaleId = $payment->pos_sale_id;
            
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

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Ensure payment belongs to current hotel
     */
    private function authorizeHotel(Payment $payment)
    {
        if ($payment->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this payment.');
        }
    }
}
