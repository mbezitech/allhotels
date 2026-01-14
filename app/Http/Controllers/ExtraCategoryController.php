<?php

namespace App\Http\Controllers;

use App\Models\ExtraCategory;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtraCategoryController extends Controller
{
    /**
     * Display a listing of extra categories.
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admins can see all extra categories, others only their hotel
        $query = ExtraCategory::query();
        if (!$isSuperAdmin) {
            if (!$hotelId) {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a hotel to view extra categories.');
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
        
        $categories = $query->with('hotel')
            ->withCount('extras')
            ->orderBy('hotel_id')
            ->orderBy('name')
            ->get();
        
        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? Hotel::orderBy('name')->get() : collect();
        
        return view('extra-categories.index', compact('categories', 'hotels', 'isSuperAdmin', 'selectedHotelId'));
    }

    /**
     * Show the form for creating a new extra category.
     */
    public function create()
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Non-super admins must have a hotel context
        if (!$isSuperAdmin && !$hotelId) {
            return redirect()->route('dashboard')
                ->with('error', 'Please select a hotel to create extra categories.');
        }
        
        return view('extra-categories.create');
    }

    /**
     * Store a newly created extra category in storage.
     */
    public function store(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Non-super admins must have a hotel context
        if (!$isSuperAdmin && !$hotelId) {
            return redirect()->route('dashboard')
                ->with('error', 'Please select a hotel to create extra categories.');
        }
        
        // Normalize checkbox value before validation
        $request->merge([
            'is_active' => $request->has('is_active') ? true : false,
        ]);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('extra_categories')->where(fn ($query) => $query->where('hotel_id', $hotelId))],
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $validated['hotel_id'] = $hotelId;
        
        $category = ExtraCategory::create($validated);

        logActivity('created', $category, "Created extra category: {$category->name}");

        // Preserve hotel filter for super admins if it was set
        $redirectParams = [];
        if ($isSuperAdmin && $hotelId) {
            $redirectParams['hotel_id'] = $hotelId;
        }
        
        return redirect()->route('extra-categories.index', $redirectParams)
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
        // Super admins can access any extra category
        if (auth()->user()->isSuperAdmin()) {
            return;
        }
        
        if ($category->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this category.');
        }
    }
}
