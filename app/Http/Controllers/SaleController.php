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
                // Check inventory for each item before creating sale
                foreach ($request->items as $item) {
                    $product = \App\Models\Product::find($item['product_id']);
                    if ($product->quantity < $item['quantity']) {
                        return response()->json([
                            'message' => 'Insufficient inventory', 
                            'errors' => [
                                'items' => ["Insufficient inventory for product: {$product->name}. Available: {$product->quantity}, Requested: {$item['quantity']}"]
                            ]
                        ], 422);
                    }
                }
                
                $sale = Sale::create($request->except('items'));
                
                foreach ($request->items as $item) {
                    $sale->items()->create($item);
                    
                    // Update product quantity
                    $product = \App\Models\Product::find($item['product_id']);
                    $product->decrement('quantity', $item['quantity']);
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
                // If items are being updated, check inventory
                if ($request->has('items')) {
                    // Get original items to calculate quantity differences
                    $originalItems = $sale->items->keyBy('id');
                    
                    foreach ($request->items as $itemData) {
                        // If this is an existing item, check quantity difference
                        if (isset($itemData['id'])) {
                            $originalItem = $originalItems->get($itemData['id']);
                            if ($originalItem) {
                                $quantityDifference = $itemData['quantity'] - $originalItem->quantity;
                                if ($quantityDifference > 0) {
                                    // Increasing quantity, check if we have enough inventory
                                    $product = \App\Models\Product::find($itemData['product_id']);
                                    if ($product->quantity < $quantityDifference) {
                                        return response()->json([
                                            'message' => 'Insufficient inventory', 
                                            'errors' => [
                                                'items' => ["Insufficient inventory for product: {$product->name}. Available: {$product->quantity}, Requested: {$quantityDifference}"]
                                            ]
                                        ], 422);
                                    }
                                }
                            }
                        } else {
                            // New item, check full quantity
                            $product = \App\Models\Product::find($itemData['product_id']);
                            if ($product->quantity < $itemData['quantity']) {
                                return response()->json([
                                    'message' => 'Insufficient inventory', 
                                    'errors' => [
                                        'items' => ["Insufficient inventory for product: {$product->name}. Available: {$product->quantity}, Requested: {$itemData['quantity']}"]
                                    ]
                                ], 422);
                            }
                        }
                    }
                    
                    // Update existing items or create new ones
                    foreach ($request->items as $itemData) {
                        if (isset($itemData['id'])) {
                            // Update existing item
                            $item = $sale->items()->find($itemData['id']);
                            if ($item) {
                                $originalQuantity = $item->quantity;
                                $item->update($itemData);
                                
                                // Update product quantity based on difference
                                $quantityDifference = $itemData['quantity'] - $originalQuantity;
                                if ($quantityDifference != 0) {
                                    $product = \App\Models\Product::find($itemData['product_id']);
                                    if ($quantityDifference > 0) {
                                        $product->decrement('quantity', $quantityDifference);
                                    } else {
                                        $product->increment('quantity', abs($quantityDifference));
                                    }
                                }
                            }
                        } else {
                            // Create new item
                            $item = $sale->items()->create($itemData);
                            
                            // Update product quantity
                            $product = \App\Models\Product::find($itemData['product_id']);
                            $product->decrement('quantity', $itemData['quantity']);
                        }
                    }
                    
                    // Delete items that are no longer in the request
                    $requestItemIds = collect($request->items)->pluck('id')->filter()->toArray();
                    $itemsToDelete = $sale->items()->whereNotIn('id', $requestItemIds)->get();
                    
                    foreach ($itemsToDelete as $itemToDelete) {
                        // Return quantity to inventory
                        $product = \App\Models\Product::find($itemToDelete->product_id);
                        $product->increment('quantity', $itemToDelete->quantity);
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
                // Return quantities to inventory
                foreach ($sale->items as $item) {
                    $product = \App\Models\Product::find($item->product_id);
                    $product->increment('quantity', $item->quantity);
                }
                
                $sale->delete();
            });
            
            return response()->json(['message' => 'Sale deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete sale', 'error' => $e->getMessage()], 500);
        }
    }
}