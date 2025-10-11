<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $sales = Sale::with(['customer', 'createdBy', 'items.product'])->get();
        return response()->json($sales);
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
            'customer_id' => 'nullable|exists:customers,id',
            'created_by' => 'nullable|exists:users,id',
            'invoice_number' => 'nullable|string|max:255',
            'sub_total' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paid' => 'required|numeric|min:0',
            'remaining' => 'required|numeric|min:0',
            'status' => 'required|in:paid,partial,credit',
            'payment_method' => 'nullable|string|max:255',
            'sale_date' => 'nullable|date',
            'branch_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0',
            'items.*.line_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                // We no longer check inventory on the product table since we moved quantity tracking
                // to purchase_items. In a real implementation, you would check available inventory
                // from purchase items.
                
                $sale = Sale::create($request->except('items'));
                
                foreach ($request->items as $item) {
                    $sale->items()->create($item);
                    
                    // No need to update product quantity anymore since we're tracking it in purchase_items
                }
                
                // Update customer balance if credit sale
                if ($request->customer_id && $request->status === 'credit') {
                    $customer = \App\Models\Customer::find($request->customer_id);
                    $customer->increment('remaining_balance', $request->remaining);
                }
                
                return response()->json(['message' => 'Sale created successfully', 'data' => $sale->load(['customer', 'createdBy', 'items.product'])], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create sale', 'error' => $e->getMessage()], 500);
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
        $sale = Sale::with(['customer', 'createdBy', 'items.product'])->findOrFail($id);
        return response()->json($sale);
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
        $sale = Sale::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'created_by' => 'nullable|exists:users,id',
            'invoice_number' => 'nullable|string|max:255',
            'sub_total' => 'sometimes|required|numeric|min:0',
            'discount' => 'sometimes|required|numeric|min:0',
            'tax' => 'sometimes|required|numeric|min:0',
            'total' => 'sometimes|required|numeric|min:0',
            'paid' => 'sometimes|required|numeric|min:0',
            'remaining' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:paid,partial,credit',
            'payment_method' => 'nullable|string|max:255',
            'sale_date' => 'nullable|date',
            'branch_id' => 'nullable|integer',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'sometimes|required|exists:sale_items,id',
            'items.*.product_id' => 'sometimes|required|exists:products,id',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.quantity' => 'sometimes|required|numeric|min:1',
            'items.*.unit_price' => 'sometimes|required|numeric|min:0',
            'items.*.discount' => 'sometimes|required|numeric|min:0',
            'items.*.line_total' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request, $sale) {
                // We no longer check inventory on the product table since we moved quantity tracking
                // to purchase_items. In a real implementation, you would check available inventory
                // from purchase items.
                
                // Update existing items or create new ones
                if ($request->has('items')) {
                    foreach ($request->items as $itemData) {
                        if (isset($itemData['id'])) {
                            // Update existing item
                            $item = $sale->items()->find($itemData['id']);
                            if ($item) {
                                $item->update($itemData);
                            }
                        } else {
                            // Create new item
                            $item = $sale->items()->create($itemData);
                        }
                    }
                    
                    // Delete items that are no longer in the request
                    $requestItemIds = collect($request->items)->pluck('id')->filter()->toArray();
                    $itemsToDelete = $sale->items()->whereNotIn('id', $requestItemIds)->get();
                    
                    foreach ($itemsToDelete as $itemToDelete) {
                        $itemToDelete->delete();
                    }
                }
                
                // Update sale details (excluding items)
                $sale->update($request->except('items'));
                
                return response()->json(['message' => 'Sale updated successfully', 'data' => $sale->load(['customer', 'createdBy', 'items.product'])]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update sale', 'error' => $e->getMessage()], 500);
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
        $sale = Sale::findOrFail($id);
        
        try {
            DB::transaction(function () use ($sale) {
                // No need to return quantities to inventory anymore since we're tracking it in purchase_items
                
                $sale->delete();
            });
            
            return response()->json(['message' => 'Sale deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete sale', 'error' => $e->getMessage()], 500);
        }
    }
}