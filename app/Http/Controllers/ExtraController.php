<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use App\Models\ExtraCategory;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExtraController extends Controller
{
    /**
     * Display a listing of extras for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admins can see all extras, others only their hotel
        $query = Extra::query();
        if (!$isSuperAdmin) {
            $query->where('hotel_id', $hotelId);
        }
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by active status
        if ($request->has('active') && $request->active !== '') {
            $query->where('is_active', $request->active);
        }

        $extras = $query->with(['category', 'hotel'])->orderBy('hotel_id')->orderBy('category_id')->orderBy('name')->get();
        
        // Pre-calculate stock balances to avoid N+1 queries
        foreach ($extras as $extra) {
            if ($extra->stock_tracked) {
                $extraHotelId = $extra->hotel_id;
                $extra->current_stock = $extra->getStockBalance($extraHotelId);
                $extra->is_low_stock = $extra->isLowStock($extraHotelId);
            }
        }

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? \App\Models\Hotel::orderBy('name')->get() : collect();
        $categories = $isSuperAdmin && $request->has('hotel_id') && $request->hotel_id
            ? \App\Models\ExtraCategory::where('hotel_id', $request->hotel_id)->orderBy('name')->get()
            : ($hotelId ? \App\Models\ExtraCategory::where('hotel_id', $hotelId)->orderBy('name')->get() : collect());

        return view('extras.index', compact('extras', 'hotels', 'categories', 'isSuperAdmin'));
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
        
        // Normalize checkbox values before validation
        $request->merge([
            'stock_tracked' => $request->has('stock_tracked') ? true : false,
            'is_active' => $request->has('is_active') ? true : false,
        ]);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => ['required', 'exists:extra_categories,id', function ($attribute, $value, $fail) use ($hotelId) {
                $category = ExtraCategory::find($value);
                if ($category && !auth()->user()->isSuperAdmin() && $category->hotel_id != $hotelId) {
                    $fail('The selected category does not belong to this hotel.');
                }
            }],
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB in kilobytes
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:50',
            'unit_custom' => 'nullable|string|max:50',
            'stock_tracked' => 'required|boolean',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $validated['hotel_id'] = $hotelId;
        
        // Handle custom unit
        if ($validated['unit'] === 'custom' && $request->has('unit_custom') && !empty($request->unit_custom)) {
            $validated['unit'] = $request->unit_custom;
        }
        unset($validated['unit_custom']); // Remove from validated as it's not a database field

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/' . $hotelId, 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
        }

        Extra::create($validated);

        return redirect()->route('extras.index')
            ->with('success', 'Product created successfully.');
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
        
        // Normalize checkbox values before validation
        $request->merge([
            'stock_tracked' => $request->has('stock_tracked') ? true : false,
            'is_active' => $request->has('is_active') ? true : false,
        ]);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => ['required', 'exists:extra_categories,id', function ($attribute, $value, $fail) use ($hotelId) {
                $category = ExtraCategory::find($value);
                if ($category && !auth()->user()->isSuperAdmin() && $category->hotel_id != $hotelId) {
                    $fail('The selected category does not belong to this hotel.');
                }
            }],
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB in kilobytes
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:50',
            'unit_custom' => 'nullable|string|max:50',
            'stock_tracked' => 'required|boolean',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);
        
        // Handle custom unit
        if ($validated['unit'] === 'custom' && $request->has('unit_custom') && !empty($request->unit_custom)) {
            $validated['unit'] = $request->unit_custom;
        }
        unset($validated['unit_custom']); // Remove from validated as it's not a database field

        // Handle image removal
        $currentImages = $extra->images ?? [];
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imageToRemove) {
                if (Storage::disk('public')->exists($imageToRemove)) {
                    Storage::disk('public')->delete($imageToRemove);
                }
                $currentImages = array_values(array_filter($currentImages, function($img) use ($imageToRemove) {
                    return $img !== $imageToRemove;
                }));
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $newImagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/' . $hotelId, 'public');
                $newImagePaths[] = $path;
            }
            $validated['images'] = array_merge($currentImages, $newImagePaths);
        } else {
            $validated['images'] = $currentImages;
        }

        $extra->update($validated);

        return redirect()->route('extras.index')
            ->with('success', 'Product updated successfully.');
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
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Ensure extra belongs to current hotel
     */
    private function authorizeHotel(Extra $extra)
    {
        // Super admins can access any extra
        if (auth()->user()->isSuperAdmin()) {
            return;
        }
        
        if ($extra->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this extra.');
        }
    }
}
