<?php

namespace Tests\Unit;

use App\DTO\Purchase\CreatePurchaseDTO;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Services\PurchaseService;
use App\Services\SupplierService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class PurchaseServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-05 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_creates_purchase_and_items_with_calculated_totals(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $callback) => $callback());

        $purchase = new class
        {
            public int $id = 12;

            public function fresh(array $relations): array
            {
                return [
                    'id' => $this->id,
                    'relations' => $relations,
                ];
            }
        };

        $purchaseRepository = Mockery::mock(PurchaseRepositoryInterface::class);
        $purchaseRepository->shouldReceive('getLastPurchaseOrderNo')
            ->once()
            ->with('20261005')
            ->andReturnNull();
        $purchaseRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $data) => $data['purchase_order_no'] === 'PO-20261005-0001'
                && $data['subtotal'] === 1600.0
                && $data['discount'] === 10.0
                && $data['tax'] === 5.0
                && $data['total'] === 1595.0
            ))
            ->andReturn($purchase);

        $purchaseItemRepository = Mockery::mock(PurchaseItemRepositoryInterface::class);
        $purchaseItemRepository->shouldReceive('create')
            ->once()
            ->with([
                'purchase_id' => 12,
                'product_id' => 1,
                'quantity' => 5,
                'cost_price' => 320.0,
                'discount' => 10.0,
                'tax' => 5.0,
                'subtotal' => 1595.0,
            ]);

        $supplierService = Mockery::mock(SupplierService::class);
        $supplierService->shouldReceive('clearStatsCache')->once();

        $service = new PurchaseService(
            $purchaseRepository,
            $purchaseItemRepository,
            $supplierService
        );

        $result = $service->create(
            new CreatePurchaseDTO(
                purchase_date: '2026-10-05',
                supplier_id: 1,
                user_id: 7,
                expected_delivery_date: '2026-10-12',
                payment_terms: 'Due in 30 days',
                notes: 'Test purchase',
            ),
            [
                [
                    'product_id' => 1,
                    'quantity' => 5,
                    'cost_price' => 320,
                    'discount' => 10,
                    'tax' => 5,
                ],
            ]
        );

        $this->assertSame(12, $result['id']);
        $this->assertSame(
            ['supplier', 'user', 'items.product'],
            $result['relations']
        );
    }
}
