<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\StorePurchasePaymentRequest;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Validation\ValidationException;

class PurchasePaymentController extends Controller
{
    public function __construct(private PurchaseService $purchaseService)
    {
    }

    public function store(StorePurchasePaymentRequest $request, Purchase $purchase)
    {
        try {
            $payment = $this->purchaseService->recordPayment(
                (int) $purchase->id,
                (int) $request->user()->id,
                $request->validated(),
            );

            return response()->json([
                'status' => 'Payment recorded',
                'data' => $payment,
            ], 201);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }
    }
}
