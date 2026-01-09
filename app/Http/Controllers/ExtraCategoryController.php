<?php

namespace App\Http\Controllers;

use App\Models\ExtraCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtraCategoryController extends Controller
{
    /**
     * Display a listing of extra categories.
     */
    public function index()
    {
        $hotelId = session('hotel_id');
        $categories = ExtraCategory::where('hotel_id', $hotelId)
            ->orderBy('name')
            ->get();
        
        return view('extra-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new extra category.
     */
    public function create()
    {
        return view('extra-categories.create');
    }

    /**
     * Store a newly created extra category in storage.
     */
    public function store(Request $request)
    {
        $hotelId = session('hotel_id');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('extra_categories')->where(fn ($query) => $query->where('hotel_id', $hotelId))],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['hotel_id'] = $hotelId;
        $validated['is_active'] = $request->has('is_active');
        
        $category = ExtraCategory::create($validated);

        logActivity('created', $category, "Created extra category: {$category->name}");

        return redirect()->route('extra-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified extra category.
     */
    public function show(ExtraCategory $extraCategory)
    {
        $this->authorizeHotel($extraCategory);
        $extraCategory->load('extras');
        return view('extra-categories.show', compact('extraCategory'));
    }

    /**
     * Show the form for editing the specified extra category.
     */
    public function edit(ExtraCategory $extraCategory)
    {
        $this->authorizeHotel($extraCategory);
        return view('extra-categories.edit', compact('extraCategory'));
    }

    /**
     * Update the specified extra category in storage.
     */
    public function update(Request $request, ExtraCategory $extraCategory)
    {
        $this->authorizeHotel($extraCategory);
        $hotelId = session('hotel_id');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('extra_categories')->where(fn ($query) => $query->where('hotel_id', $hotelId))->ignore($extraCategory->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $extraCategory->update($validated);

        logActivity('updated', $extraCategory, "Updated extra category: {$extraCategory->name}");

        return redirect()->route('extra-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified extra category from storage.
     */
    public function destroy(ExtraCategory $extraCategory)
    {
        $this->authorizeHotel($extraCategory);

        if ($extraCategory->extras()->exists()) {
            return back()->with('error', 'Cannot delete category: extras are associated with it.');
        }

        $categoryName = $extraCategory->name;
        $extraCategory->delete();

        logActivity('deleted', null, "Deleted extra category: {$categoryName}", ['category_id' => $extraCategory->id]);

        return redirect()->route('extra-categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Ensure category belongs to current hotel
     */
    private function authorizeHotel(ExtraCategory $category)
    {
        if ($category->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this category.');
        }
    }
}
