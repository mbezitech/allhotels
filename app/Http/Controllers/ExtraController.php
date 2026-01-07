<?php

namespace App\Http\Controllers;

use App\Models\Extra;
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
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by active status
        if ($request->has('active') && $request->active !== '') {
            $query->where('is_active', $request->active);
        }

        $extras = $query->orderBy('category')->orderBy('name')->get();

        return view('extras.index', compact('extras'));
    }

    /**
     * Show the form for creating a new extra
     */
    public function create()
    {
        return view('extras.create');
    }

    /**
     * Store a newly created extra
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_tracked' => 'boolean',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['hotel_id'] = session('hotel_id');
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
        
        return view('extras.edit', compact('extra'));
    }

    /**
     * Update the specified extra
     */
    public function update(Request $request, Extra $extra)
    {
        $this->authorizeHotel($extra);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
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
