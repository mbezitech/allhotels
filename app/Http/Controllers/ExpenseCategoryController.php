<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Hotel;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of expense categories for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admins can see all categories, others only their hotel
        $query = ExpenseCategory::query();
        if (!$isSuperAdmin) {
            $query->where('hotel_id', $hotelId);
        }
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
        }

        // Filter by active status
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $categories = $query->with(['hotel', 'expenses'])
            ->withCount('expenses')
            ->orderBy('hotel_id')
            ->orderBy('name')
            ->get();

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? Hotel::orderBy('name')->get() : collect();
        $selectedHotelId = $isSuperAdmin && $request->has('hotel_id') && $request->hotel_id
            ? $request->hotel_id
            : $hotelId;

        return view('expense-categories.index', compact('categories', 'hotels', 'isSuperAdmin', 'selectedHotelId'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $hotelId = session('hotel_id');
        
        if (!$hotelId) {
            return redirect()->route('dashboard')
                ->with('error', 'Please select a hotel to add expense categories.');
        }
        
        return view('expense-categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $hotelId = session('hotel_id');
        
        if (!$hotelId) {
            return back()->with('error', 'Please select a hotel.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,NULL,id,hotel_id,' . $hotelId,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['hotel_id'] = $hotelId;
        $validated['is_active'] = $request->has('is_active') ? true : true; // Default to active

        $category = ExpenseCategory::create($validated);

        // Log activity
        logActivity('created', $category, "Created expense category: {$category->name}");

        return redirect()->route('expense-categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        $this->authorizeHotel($expenseCategory);
        
        return view('expense-categories.edit', compact('expenseCategory'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $this->authorizeHotel($expenseCategory);
        
        $hotelId = session('hotel_id');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id . ',id,hotel_id,' . $hotelId,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $oldValues = $expenseCategory->toArray();
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $expenseCategory->update($validated);

        // Log activity
        logActivity('updated', $expenseCategory, "Updated expense category: {$expenseCategory->name}", null, $oldValues, $expenseCategory->toArray());

        return redirect()->route('expense-categories.index')
            ->with('success', 'Expense category updated successfully.');
    }

    /**
     * Remove the specified category
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        $this->authorizeHotel($expenseCategory);
        
        // Check if category has expenses
        if ($expenseCategory->expenses()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing expenses. Please deactivate it instead.');
        }
        
        $name = $expenseCategory->name;
        $expenseCategory->delete();

        // Log activity
        logActivity('deleted', null, "Deleted expense category: {$name}", ['category_id' => $expenseCategory->id]);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Expense category deleted successfully.');
    }

    /**
     * Authorize that the category belongs to the current hotel (or user is super admin)
     */
    private function authorizeHotel(ExpenseCategory $expenseCategory)
    {
        if (auth()->user()->isSuperAdmin()) {
            return;
        }

        $hotelId = session('hotel_id');
        if ($expenseCategory->hotel_id != $hotelId) {
            abort(403, 'Unauthorized access to this category.');
        }
    }
}
