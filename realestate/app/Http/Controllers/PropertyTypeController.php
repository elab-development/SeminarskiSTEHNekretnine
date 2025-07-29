<?php

namespace App\Http\Controllers;

use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertyTypeResource;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/property-types",
     *     summary="Get all property types",
     *     tags={"Property Types"},
     *     @OA\Response(response=200, description="List of property types"),
     *     @OA\Response(response=404, description="No property types found")
     * )
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
     * @OA\Get(
     *     path="/api/property-types/{id}/properties",
     *     summary="Get all properties by property type ID",
     *     tags={"Property Types"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the property type",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of properties for the given property type",
     *     ),
     *     @OA\Response(response=404, description="No properties found for this property type")
     * )
     */
    public function getPropertiesByType($id)
    {
        $properties = \App\Models\Property::with(['propertyType', 'listedBy'])
            ->where('property_type_id', $id)
            ->get();

        if ($properties->isEmpty()) {
            return response()->json(['message' => 'No properties found for this property type.'], 404);
        }

        return response()->json([
            'properties' => PropertyResource::collection($properties),
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
     * @OA\Post(
     *     path="/api/property-types",
     *     summary="Create a new property type",
     *     tags={"Property Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Property type created"),
     *     @OA\Response(response=403, description="Only admin can create property types"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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
     * @OA\Get(
     *     path="/api/property-types/{id}",
     *     summary="Get a specific property type",
     *     tags={"Property Types"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Property type found"),
     *     @OA\Response(response=404, description="Not found")
     * )
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
     * @OA\Put(
     *     path="/api/property-types/{id}",
     *     summary="Update a property type",
     *     tags={"Property Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Property type updated"),
     *     @OA\Response(response=403, description="Only admin can update property types")
     * )
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
     * @OA\Delete(
     *     path="/api/property-types/{id}",
     *     summary="Delete a property type",
     *     tags={"Property Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Property type deleted"),
     *     @OA\Response(response=403, description="Only admin can delete property types")
     * )
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