<?php

namespace App\Http\Controllers;

use App\Http\Resources\PropertyTypeResource;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = PropertyType::all();
        if (is_null($types) || count($types) === 0) {
            return response()->json('No property types found!', 404);
        }
        return response()->json([
            'Property types' => PropertyTypeResource::collection($types),
        ]);
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
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Only admin can create new property types!'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_types',
            'description' => 'nullable|string',
        ]);

        $propertyType = PropertyType::create($validated);
        return response()->json([
            'Property type' =>  new PropertyTypeResource($propertyType)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $propertyType = PropertyType::find($id);
        if (is_null($propertyType)) {
            return response()->json('Property type not found', 404);
        }
        return response()->json([
            'Property type' => new PropertyTypeResource($propertyType)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PropertyType $propertyType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PropertyType $propertyType)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Only admin can update property types!'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_types,name,' . $propertyType->id,
            'description' => 'nullable|string',
        ]);

        $propertyType->update($validated);
        return response()->json([
            'Property type' => new PropertyTypeResource($propertyType)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PropertyType $propertyType)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Only admin can delete property types!'], 403);
        }

        $propertyType->delete();
        return response()->json(['message' => 'Property type deleted successfully!']);
    }
}