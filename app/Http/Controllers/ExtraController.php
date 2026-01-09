<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use App\Models\ExtraCategory;
use Illuminate\Http\Request;

class ExtraController extends Controller
{
    /**
     * Display a listing of extras for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        
        $query = Extra::where('hotel_id', $hotelId);

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by active status
        if ($request->has('active') && $request->active !== '') {
            $query->where('is_active', $request->active);
        }

        $extras = $query->with('category')->orderBy('category_id')->orderBy('name')->get();

        return view('extras.index', compact('extras'));
    }

    /**
     * Show the form for creating a new extra
     */
    public function create()
    {
        $hotelId = session('hotel_id');
        $categories = ExtraCategory::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('extras.create', compact('categories'));
    }

    /**
     * Store a newly created extra
     */
    public function store(Request $request)
    {
        $hotelId = session('hotel_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => ['required', 'exists:extra_categories,id', function ($attribute, $value, $fail) use ($hotelId) {
                $category = ExtraCategory::find($value);
                if ($category && $category->hotel_id != $hotelId) {
                    $fail('The selected category does not belong to this hotel.');
                }
            }],
            'price' => 'required|numeric|min:0',
            'stock_tracked' => 'boolean',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['hotel_id'] = $hotelId;
        $validated['stock_tracked'] = $request->has('stock_tracked');
        $validated['is_active'] = $request->has('is_active');

        Extra::create($validated);

        return redirect()->route('extras.index')
            ->with('success', 'Extra created successfully.');
    }

    /**
     * Display the specified extra
     */
    public function show(Extra $extra)
    {
        $this->authorizeHotel($extra);
        
        return view('extras.show', compact('extra'));
    }

    /**
     * Show the form for editing the specified extra
     */
    public function edit(Extra $extra)
    {
        $this->authorizeHotel($extra);
        $hotelId = session('hotel_id');
        $categories = ExtraCategory::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('extras.edit', compact('extra', 'categories'));
    }

    /**
     * Update the specified extra
     */
    public function update(Request $request, Extra $extra)
    {
        $this->authorizeHotel($extra);

        $hotelId = session('hotel_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => ['required', 'exists:extra_categories,id', function ($attribute, $value, $fail) use ($hotelId) {
                $category = ExtraCategory::find($value);
                if ($category && $category->hotel_id != $hotelId) {
                    $fail('The selected category does not belong to this hotel.');
                }
            }],
            'price' => 'required|numeric|min:0',
            'stock_tracked' => 'boolean',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['stock_tracked'] = $request->has('stock_tracked');
        $validated['is_active'] = $request->has('is_active');

        $extra->update($validated);

        return redirect()->route('extras.index')
            ->with('success', 'Extra updated successfully.');
    }

    /**
     * Remove the specified extra
     */
    public function destroy(Extra $extra)
    {
        $this->authorizeHotel($extra);

        // Check if extra has been used in any sales
        $hasSales = $extra->posSaleItems()->exists();

        if ($hasSales) {
            return redirect()->route('extras.index')
                ->with('error', 'Cannot delete extra that has been used in sales.');
        }

        $extra->delete();

        return redirect()->route('extras.index')
            ->with('success', 'Extra deleted successfully.');
    }

    /**
     * Ensure extra belongs to current hotel
     */
    private function authorizeHotel(Extra $extra)
    {
        if ($extra->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this extra.');
        }
    }
}
