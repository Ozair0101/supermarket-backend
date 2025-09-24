<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $payments = Payment::with(['sale', 'purchase'])->get();
        return response()->json($payments);
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
            'sale_id' => 'nullable|exists:sales,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,bank_transfer',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment = Payment::create($request->all());
        return response()->json($payment, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $payment = Payment::with(['sale', 'purchase'])->findOrFail($id);
        return response()->json($payment);
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
        $payment = Payment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'sale_id' => 'nullable|exists:sales,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_method' => 'sometimes|required|string|in:cash,card,bank_transfer',
            'payment_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment->update($request->all());
        return response()->json($payment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return response()->json(null, 204);
    }
}