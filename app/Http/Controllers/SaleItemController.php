<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaleItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $saleItems = SaleItem::with(['sale', 'product'])->get();
        return response()->json($saleItems);
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
            'sale_id' => 'required|exists:sales,id',
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'line_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $saleItem = SaleItem::create($request->all());
        return response()->json($saleItem->load(['sale', 'product']), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $saleItem = SaleItem::with(['sale', 'product'])->findOrFail($id);
        return response()->json($saleItem);
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
        $saleItem = SaleItem::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'sale_id' => 'sometimes|required|exists:sales,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'quantity' => 'sometimes|required|numeric|min:0',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'discount' => 'sometimes|required|numeric|min:0',
            'line_total' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $saleItem->update($request->all());
        return response()->json($saleItem->load(['sale', 'product']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $saleItem = SaleItem::findOrFail($id);
        $saleItem->delete();
        return response()->json(null, 204);
    }
}
