<?php

namespace App\Http\Controllers;

use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/properties",
     *     summary="Get a list of properties with optional filters and pagination",
     *     tags={"Properties"},
     *     @OA\Parameter(name="title", in="query", description="Filter by title", @OA\Schema(type="string")),
     *     @OA\Parameter(name="description", in="query", description="Filter by description", @OA\Schema(type="string")),
     *     @OA\Parameter(name="city", in="query", description="Filter by city", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="price_min", in="query", description="Minimum price", @OA\Schema(type="number")),
     *     @OA\Parameter(name="price_max", in="query", description="Maximum price", @OA\Schema(type="number")),
     *     @OA\Parameter(name="property_type", in="query", description="Property type name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", description="Results per page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", description="Page number", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of properties")
     * )
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

        return response()->json([
            'properties' => PropertyResource::collection($properties)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/properties/search",
     *     summary="Search properties by query and optional sort by price",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Search string matching title, description, city, address, status, type name, or lister name/email",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by_price",
     *         in="query",
     *         required=false,
     *         description="Optional sorting by price. Use 'asc' or 'desc'.",
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(response=200, description="List of properties"),
     *     @OA\Response(response=400, description="Search query is required"),
     *     @OA\Response(response=404, description="No matching properties found")
     * )
     */
    public function search(Request $request)
    {
        $queryParam = $request->query('query');
        $sortByPrice = $request->query('sort_by_price'); // 'asc' or 'desc'

        if (!$queryParam) {
            return response()->json(['error' => 'Search query is required.'], 400);
        }

        $properties = Property::with(['propertyType', 'listedBy'])
            ->where(function ($q) use ($queryParam) {
                $q->where('title', 'like', '%' . $queryParam . '%')
                    ->orWhere('description', 'like', '%' . $queryParam . '%')
                    ->orWhere('city', 'like', '%' . $queryParam . '%')
                    ->orWhere('address', 'like', '%' . $queryParam . '%')
                    ->orWhere('status', 'like', '%' . $queryParam . '%')
                    ->orWhereHas('propertyType', function ($typeQuery) use ($queryParam) {
                        $typeQuery->where('name', 'like', '%' . $queryParam . '%');
                    })
                    ->orWhereHas('listedBy', function ($userQuery) use ($queryParam) {
                        $userQuery->where('name', 'like', '%' . $queryParam . '%')
                            ->orWhere('email', 'like', '%' . $queryParam . '%');
                    });
            });

        if ($sortByPrice === 'asc' || $sortByPrice === 'desc') {
            $properties = $properties->orderBy('price', $sortByPrice);
        }

        $results = $properties->get();

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No matching properties found.'], 404);
        }

        return response()->json([
            'properties' => PropertyResource::collection($results)
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
     *     path="/api/properties",
     *     summary="Create a new property",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "price", "address", "city", "status", "property_type_id"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="status", type="string", enum={"available", "sold", "pending"}),
     *             @OA\Property(property="property_type_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Property created successfully"),
     *     @OA\Response(response=403, description="Only admin can create properties"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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
     * @OA\Get(
     *     path="/api/properties/{id}",
     *     summary="Get a specific property",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Property found"),
     *     @OA\Response(response=404, description="Property not found")
     * )
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
     * @OA\Put(
     *     path="/api/properties/{id}",
     *     summary="Update an existing property",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "price", "address", "city", "status", "property_type_id"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="city", type="string"),
     *             @OA\Property(property="status", type="string", enum={"available", "sold", "pending"}),
     *             @OA\Property(property="property_type_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Property updated successfully"),
     *     @OA\Response(response=403, description="Only admin can update properties")
     * )
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
     * @OA\Delete(
     *     path="/api/properties/{id}",
     *     summary="Delete a property",
     *     tags={"Properties"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Property deleted"),
     *     @OA\Response(response=403, description="Only admin can delete properties")
     * )
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