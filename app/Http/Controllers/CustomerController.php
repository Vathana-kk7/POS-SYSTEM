<?php

namespace App\Http\Controllers;

use App\DTO\Customer\CreateCustomerDTO;
use App\DTO\Customer\UpdateCustomerDTO;
use App\Http\Requests\Customer\ImportCustomerRequest;
use App\Http\Requests\Customer\ExportCustomerRequest;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $CustomerService
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        try {
            $perPage = (int) ($filters['per_page'] ?? 10);
            unset($filters['page'], $filters['per_page']);
            $result = $this->CustomerService->all($perPage, $filters);
            return response()->json([
                "status" => "success",
                "data" => $result,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    public function stats()
    {
        try {
            return response()->json([
                "status" => "success",
                "data" => $this->CustomerService->stats(),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function import(ImportCustomerRequest $request)
    {
        try {
            $result = $this->CustomerService->importCustomers($request->file('file'));

            if ($result['imported'] === 0) {
                if ($result['errors'] !== []) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No valid customers could be imported. See row errors.',
                        'errors' => $result['errors'],
                    ], 422);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'The Excel file contains no customer rows.',
                ], 422);
            }

            $reportedErrors = array_slice($result['errors'], 0, 100, true);
            $message = $result['skipped'] > 0
                ? "{$result['imported']} customers imported; {$result['skipped']} invalid or duplicate rows skipped."
                : "{$result['imported']} customers imported successfully.";

            if (count($result['errors']) > count($reportedErrors)) {
                $message .= ' Showing the first 100 row issues.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'errors' => $reportedErrors,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer import failed: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(ExportCustomerRequest $request)
    {
        try {
            return $this->CustomerService->exportExcel($request->validated());
        } catch (\Throwable $th) {
            \Log::error('Customer Excel export failed', ['message' => $th->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Customer Excel export failed.',
            ], 500);
        }
    }

    public function exportPdf(ExportCustomerRequest $request)
    {
        try {
            return $this->CustomerService->exportPdf($request->validated());
        } catch (\Throwable $th) {
            \Log::error('Customer PDF export failed', ['message' => $th->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Customer PDF export failed.',
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            $result=$this->CustomerService->create(
                new CreateCustomerDTO(
                    $request->name,
                    $request->phone,
                    $request->address,
                    $request->email,
                    $request->group,
                    $request->status,
                )
            );
            return response()->json([
                "status"=>"Create Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $result=$this->CustomerService->findById($id);
            return response()->json([
                "status"=>"Create Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        try {
            $validated = $request->validated();
            $result=$this->CustomerService->update(
                (string) $customer->getKey(),
                new UpdateCustomerDTO(
                    $validated["name"],
                    $validated["phone"],
                    array_key_exists("address", $validated) ? $validated["address"] : $customer->address,
                    array_key_exists("email", $validated) ? $validated["email"] : $customer->email,
                    $validated["group"] ?? $customer->group,
                    $validated["status"] ?? $customer->status,
                )
            );
            return response()->json([
                "message" => "Customer updated successfully",
                "data" => $result->refresh(),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        if ($customer->orders()->exists()) {
            return response()->json([
                "message" => "This customer cannot be deleted because associated orders exist.",
            ], 409);
        }

        try {
            $this->CustomerService->delete((string) $customer->getKey());
        } catch (QueryException $exception) {
            if (!$customer->orders()->exists()) {
                throw $exception;
            }

            return response()->json([
                "message" => "This customer cannot be deleted because associated orders exist.",
            ], 409);
        }

        return response()->json([
            "message" => "Customer deleted successfully",
        ]);
    }
}
