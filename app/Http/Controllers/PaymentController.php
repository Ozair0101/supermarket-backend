<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $payments = Payment::with(['payable'])->get();
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
            'payable_type' => 'required|string|in:App\Models\Sale,App\Models\Purchase',
            'payable_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|string|in:cash,card,bank_transfer',
            'paid_at' => 'required|date',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $payment = $request->all();
            $payment['user_id'] = Auth::id(); // Using Auth::id() directly
            $payment = Payment::create($payment);

            // Update the payable's paid and remaining amounts
            $this->updatePayableAmounts($request->payable_type, $request->payable_id, $request->amount);

            return response()->json(['message' => 'Payment created successfully', 'data' => $payment], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create payment', 'error' => $e->getMessage()], 500);
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
        $payment = Payment::with(['payable'])->findOrFail($id);
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
            'payable_type' => 'sometimes|required|string|in:App\Models\Sale,App\Models\Purchase',
            'payable_id' => 'sometimes|required|integer',
            'amount' => 'sometimes|required|numeric|min:0',
            'method' => 'sometimes|required|string|in:cash,card,bank_transfer',
            'paid_at' => 'sometimes|required|date',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $oldAmount = $payment->amount;
            $payment->update($request->all());

            // If the amount changed, update the payable's paid and remaining amounts
            if ($request->has('amount') && $oldAmount != $request->amount) {
                $this->updatePayableAmounts($payment->payable_type, $payment->payable_id, $request->amount - $oldAmount);
            }

            return response()->json(['message' => 'Payment updated successfully', 'data' => $payment]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update payment', 'error' => $e->getMessage()], 500);
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
        $payment = Payment::findOrFail($id);

        try {
            // Store the payment details before deleting for updating payable amounts
            $payableType = $payment->payable_type;
            $payableId = $payment->payable_id;
            $amount = $payment->amount;

            $payment->delete();

            // Update the payable's paid and remaining amounts (subtract the deleted payment)
            $this->updatePayableAmounts($payableType, $payableId, -$amount);

            return response()->json(['message' => 'Payment deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete payment', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the paid and remaining amounts for a payable (Sale or Purchase).
     *
     * @param string $payableType
     * @param int $payableId
     * @param float $amountChange
     * @return void
     */
    private function updatePayableAmounts($payableType, $payableId, $amountChange)
    {
        if ($payableType === 'App\Models\Sale') {
            $sale = Sale::findOrFail($payableId);
            $newPaid = $sale->paid + $amountChange;
            $newRemaining = $sale->total - $newPaid;

            // Ensure paid amount doesn't exceed total
            $newPaid = min($newPaid, $sale->total);
            $newRemaining = max($newRemaining, 0);

            // Update status based on paid amount
            $status = 'paid';
            if ($newPaid == 0) {
                $status = 'unpaid';
            } elseif ($newPaid < $sale->total) {
                $status = 'partial';
            }

            $sale->update([
                'paid' => $newPaid,
                'remaining' => $newRemaining,
                'status' => $status
            ]);
        } elseif ($payableType === 'App\Models\Purchase') {
            $purchase = Purchase::findOrFail($payableId);
            $newPaid = $purchase->paid + $amountChange;
            $newRemaining = $purchase->total - $newPaid;

            // Ensure paid amount doesn't exceed total
            $newPaid = min($newPaid, $purchase->total);
            $newRemaining = max($newRemaining, 0);

            // Update status based on paid amount
            $status = 'paid';
            if ($newPaid == 0) {
                $status = 'credit'; // For purchases, unpaid is called "credit"
            } elseif ($newPaid < $purchase->total) {
                $status = 'partial';
            }

            $purchase->update([
                'paid' => $newPaid,
                'remaining' => $newRemaining,
                'status' => $status
            ]);
        }
    }
}
