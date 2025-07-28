<?php

namespace App\Http\Controllers;

use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Property::with(['propertyType', 'listedBy']);

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->query('title') . '%');
        }

        if ($request->has('description')) {
            $query->where('description', 'like', '%' . $request->query('description') . '%');
        }

        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->query('city') . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->query('price_min'));
        }

        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->query('price_max'));
        }

        if ($request->has('property_type')) {
            $query->whereHas('propertyType', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->query('property_type') . '%');
            });
        }

        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $properties = $query->paginate($perPage, ['*'], 'page', $page);

        return PropertyResource::collection($properties);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Only admin can create properties!'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'status' => 'required|string|in:available,sold,pending',
            'property_type_id' => 'required|exists:property_types,id',
        ]);

        $validated['listed_by'] = $user->id;

        $property = Property::create($validated);

        return response()->json([
            'message' => 'Property created successfully!',
            'property' => new PropertyResource($property),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $property = Property::with(['propertyType', 'listedBy'])->find($id);

        if (!$property) {
            return response()->json(['message' => 'Property not found!'], 404);
        }

        return response()->json([
            'property' => new PropertyResource($property),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Only admin can update properties!'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'status' => 'required|string|in:available,sold,pending',
            'property_type_id' => 'required|exists:property_types,id',
        ]);

        $property->update($validated);

        return response()->json([
            'message' => 'Property updated successfully!',
            'property' => new PropertyResource($property),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Only admin can delete properties!'], 403);
        }

        $property->delete();

        return response()->json(['message' => 'Property deleted successfully!']);
    }
}