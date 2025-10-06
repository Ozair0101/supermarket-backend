<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $adjustments = InventoryAdjustment::with('product')->get();
        return response()->json($adjustments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity_change' => 'required|integer',
            'reason' => 'required|string',
            'adjusted_by' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $adjustment = InventoryAdjustment::create($request->all());
            return response()->json(['message' => 'Inventory adjustment created successfully', 'data' => $adjustment], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create inventory adjustment', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $adjustment = InventoryAdjustment::with('product')->findOrFail($id);
        return response()->json($adjustment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'product_id' => 'sometimes|required|exists:products,id',
            'quantity_change' => 'sometimes|required|integer',
            'reason' => 'sometimes|required|string',
            'adjusted_by' => 'sometimes|required|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $adjustment->update($request->all());
            return response()->json(['message' => 'Inventory adjustment updated successfully', 'data' => $adjustment]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update inventory adjustment', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);
        
        try {
            $adjustment->delete();
            return response()->json(['message' => 'Inventory adjustment deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete inventory adjustment', 'error' => $e->getMessage()], 500);
        }
    }
}