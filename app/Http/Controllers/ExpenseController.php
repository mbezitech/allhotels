<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admins can see all expenses, others only their hotel
        $query = Expense::query();
        if (!$isSuperAdmin) {
            $query->where('hotel_id', $hotelId);
        }
        
        $query->with(['category', 'addedBy', 'hotel']);
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->where('expense_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        // Filter by amount range
        if ($request->has('amount_from') && $request->amount_from) {
            $query->where('amount', '>=', $request->amount_from);
        }
        if ($request->has('amount_to') && $request->amount_to) {
            $query->where('amount', '<=', $request->amount_to);
        }

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? Hotel::orderBy('name')->get() : collect();
        
        // Get categories for filter
        $selectedHotelId = $isSuperAdmin && $request->has('hotel_id') && $request->hotel_id
            ? $request->hotel_id
            : $hotelId;
        
        $categories = $selectedHotelId
            ? ExpenseCategory::where('hotel_id', $selectedHotelId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect();

        return view('expenses.index', compact('expenses', 'hotels', 'categories', 'isSuperAdmin'));
    }

    /**
     * Show the form for creating a new expense
     */
    public function create()
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        if (!$hotelId && !$isSuperAdmin) {
            return redirect()->route('dashboard')
                ->with('error', 'Please select a hotel to add expenses.');
        }
        
        // For super admins without hotel context, show message
        if ($isSuperAdmin && !$hotelId) {
            return redirect()->route('expenses.index')
                ->with('error', 'Please select a hotel to add expenses.');
        }
        
        $categories = ExpenseCategory::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('expenses.create', compact('categories'));
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        $hotelId = session('hotel_id');
        
        if (!$hotelId) {
            return back()->with('error', 'Please select a hotel.');
        }
        
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'expense_category_id' => ['required', 'exists:expense_categories,id', function ($attribute, $value, $fail) use ($hotelId) {
                $category = ExpenseCategory::find($value);
                if ($category && !auth()->user()->isSuperAdmin() && $category->hotel_id != $hotelId) {
                    $fail('The selected category does not belong to this hotel.');
                }
            }],
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,mobile',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ]);

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('expenses', 'public');
            $validated['attachment'] = $path;
        }

        $validated['hotel_id'] = $hotelId;
        $validated['added_by'] = auth()->id();

        $expense = Expense::create($validated);

        // Log activity
        logActivity('created', $expense, "Added expense: {$expense->description} - $" . number_format($expense->amount, 2));

        return redirect()->route('expenses.index')
            ->with('success', 'Expense added successfully.');
    }

    /**
     * Display the specified expense
     */
    public function show(Expense $expense)
    {
        $this->authorizeHotel($expense);
        
        $expense->load(['category', 'addedBy', 'hotel']);
        
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense
     */
    public function edit(Expense $expense)
    {
        $this->authorizeHotel($expense);
        
        $hotelId = session('hotel_id');
        $categories = ExpenseCategory::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, Expense $expense)
    {
        $this->authorizeHotel($expense);
        
        $hotelId = session('hotel_id');
        
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'expense_category_id' => ['required', 'exists:expense_categories,id', function ($attribute, $value, $fail) use ($hotelId) {
                $category = ExpenseCategory::find($value);
                if ($category && !auth()->user()->isSuperAdmin() && $category->hotel_id != $hotelId) {
                    $fail('The selected category does not belong to this hotel.');
                }
            }],
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,mobile',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ]);

        $oldValues = $expense->toArray();

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($expense->attachment) {
                Storage::disk('public')->delete($expense->attachment);
            }
            
            $file = $request->file('attachment');
            $path = $file->store('expenses', 'public');
            $validated['attachment'] = $path;
        }

        $expense->update($validated);

        // Log activity
        logActivity('updated', $expense, "Updated expense: {$expense->description} - $" . number_format($expense->amount, 2), null, $oldValues, $expense->toArray());

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense
     */
    public function destroy(Expense $expense)
    {
        $this->authorizeHotel($expense);
        
        $description = $expense->description;
        $amount = $expense->amount;
        $expenseId = $expense->id;
        
        // Delete attachment if exists
        if ($expense->attachment) {
            Storage::disk('public')->delete($expense->attachment);
        }
        
        $expense->delete();

        // Log activity
        logActivity('deleted', null, "Deleted expense: {$description} - $" . number_format($amount, 2), ['expense_id' => $expenseId]);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Authorize that the expense belongs to the current hotel (or user is super admin)
     */
    private function authorizeHotel(Expense $expense)
    {
        if (auth()->user()->isSuperAdmin()) {
            return;
        }

        $hotelId = session('hotel_id');
        if ($expense->hotel_id != $hotelId) {
            abort(403, 'Unauthorized access to this expense.');
        }
    }
}
