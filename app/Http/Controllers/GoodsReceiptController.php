<?php

namespace App\Http\Controllers;

use App\DTO\GoodsReceipt\ReceiveGoodsDTO;
use App\Http\Requests\GoodsReceipt\ReceiveGoodsRequest;
use App\Models\Purchase;
use App\Services\GoodsReceiptService;
use Illuminate\Validation\ValidationException;

class GoodsReceiptController extends Controller
{
    public function __construct(private GoodsReceiptService $goodsReceiptService)
    {
    }

    public function index()
    {
        return response()->json([
            'status' => 'GetAll Success',
            'data' => $this->goodsReceiptService->all(),
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'status' => 'Get Success',
            'data' => $this->goodsReceiptService->findById($id),
        ]);
    }

    public function store(ReceiveGoodsRequest $request)
    {
        return $this->receiveValidated($request, (int) $request->validated('purchase_id'));
    }

    public function receivePurchase(ReceiveGoodsRequest $request, Purchase $purchase)
    {
        return $this->receiveValidated($request, (int) $purchase->id);
    }

    private function receiveValidated(ReceiveGoodsRequest $request, int $purchaseId)
    {
        $data = $request->validated();

        try {
            $receipt = $this->goodsReceiptService->receive(
                new ReceiveGoodsDTO(
                    purchase_id: $purchaseId,
                    user_id: (int) $request->user()->id,
                    items: $data['items'],
                )
            );

            return response()->json([
                'status' => 'Goods received successfully',
                'data' => $receipt,
            ], 201);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }
    }
}
