<?php

namespace App\Http\Controllers;

use App\Http\Resources\InquiryResource;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Display the specified resource.
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inquiry $inquiry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inquiry $inquiry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
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