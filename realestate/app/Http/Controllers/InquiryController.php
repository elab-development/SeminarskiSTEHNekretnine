<?php

namespace App\Http\Controllers;

use App\Http\Resources\InquiryResource;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/inquiries",
     *     summary="Get all inquiries (admin) or own inquiries (user)",
     *     tags={"Inquiries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of inquiries"),
     *     @OA\Response(response=404, description="No inquiries found")
     * )
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $inquiries = Inquiry::with(['property', 'user'])->get();
        } else {
            $inquiries = Inquiry::with(['property', 'user'])
                ->where('user_id', $user->id)
                ->get();
        }

        if ($inquiries->isEmpty()) {
            return response()->json(['message' => 'No inquiries found!'], 404);
        }

        return response()->json([
            'inquiries' => InquiryResource::collection($inquiries)
        ]);
    }

    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/inquiries",
     *     summary="Submit a property inquiry",
     *     tags={"Inquiries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_id", "message"},
     *             @OA\Property(property="property_id", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Inquiry submitted successfully"),
     *     @OA\Response(response=403, description="Only regular users can create inquiries"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'user') {
            return response()->json(['message' => 'Only regular users can create inquiries!'], 403);
        }

        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'message' => 'required|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $inquiry = Inquiry::create([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'],
            'property_id' => $validated['property_id'],
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Inquiry submitted successfully!',
            'inquiry' => new InquiryResource($inquiry),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/inquiries/{id}",
     *     summary="Get a specific inquiry by ID",
     *     tags={"Inquiries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Inquiry ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Inquiry details"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Inquiry not found")
     * )
     */
    public function show($id)
    {
        $inquiry = Inquiry::with(['property', 'user'])->find($id);

        if (!$inquiry) {
            return response()->json(['message' => 'Inquiry not found!'], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'admin' && $inquiry->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized!'], 403);
        }

        return response()->json([
            'inquiry' => new InquiryResource($inquiry),
        ]);
    }

    public function edit(Inquiry $inquiry)
    {
        //
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        //
    }

    /**
     * @OA\Delete(
     *     path="/api/inquiries/{id}",
     *     summary="Delete an inquiry by ID",
     *     tags={"Inquiries"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Inquiry ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Inquiry deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Inquiry not found")
     * )
     */
    public function destroy($id)
    {
        $inquiry = Inquiry::find($id);

        if (!$inquiry) {
            return response()->json(['message' => 'Inquiry not found!'], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'admin' && $inquiry->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized!'], 403);
        }

        $inquiry->delete();

        return response()->json(['message' => 'Inquiry deleted successfully!']);
    }
}