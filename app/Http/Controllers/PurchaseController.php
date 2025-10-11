<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'createdBy', 'items.product'])->get();
        return response()->json($purchases);
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
            'supplier_id' => 'required|exists:suppliers,id',
            'created_by' => 'nullable|exists:users,id',
            'invoice_number' => 'nullable|string|max:255',
            'sub_total' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paid' => 'required|numeric|min:0',
            'remaining' => 'required|numeric|min:0',
            'status' => 'required|in:paid,partial,credit',
            'purchase_date' => 'nullable|date',
            'branch_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0',
            'items.*.line_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $purchase = Purchase::create($request->except('items'));
                
                foreach ($request->items as $item) {
                    $purchase->items()->create($item);
                    
                    // No need to update product quantity anymore since we're tracking it in purchase_items
                }
                
                // Update supplier balance
                $supplier = \App\Models\Supplier::find($request->supplier_id);
                $supplier->increment('remaining_balance', $request->remaining);
                
                return response()->json(['message' => 'Purchase created successfully', 'data' => $purchase->load(['supplier', 'createdBy', 'items.product'])], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create purchase', 'error' => $e->getMessage()], 500);
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
        $purchase = Purchase::with(['supplier', 'createdBy', 'items.product'])->findOrFail($id);
        return response()->json($purchase);
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
        $purchase = Purchase::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'created_by' => 'nullable|exists:users,id',
            'invoice_number' => 'nullable|string|max:255',
            'sub_total' => 'sometimes|required|numeric|min:0',
            'discount' => 'sometimes|required|numeric|min:0',
            'tax' => 'sometimes|required|numeric|min:0',
            'total' => 'sometimes|required|numeric|min:0',
            'paid' => 'sometimes|required|numeric|min:0',
            'remaining' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:paid,partial,credit',
            'purchase_date' => 'nullable|date',
            'branch_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $purchase->update($request->except('items'));
            return response()->json(['message' => 'Purchase updated successfully', 'data' => $purchase->load(['supplier', 'createdBy', 'items.product'])]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update purchase', 'error' => $e->getMessage()], 500);
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
        $purchase = Purchase::findOrFail($id);
        
        try {
            $purchase->delete();
            return response()->json(['message' => 'Purchase deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete purchase', 'error' => $e->getMessage()], 500);
        }
    }
}