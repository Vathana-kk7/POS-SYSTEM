<?php
namespace App\Repositories;

use App\Models\Customer;
use App\Models\Order;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;


class CustomerRepository implements CustomerRepositoryInterface{

    // #[Override]
    public function create(array $data)
    {
        return Customer::create($data);
    }
    public function update(string $id,array $data)
    {
        $result=Customer::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function all(int $perPage = 10, array $filters = [])
    {
        $query = Customer::query()->orderByDesc('id');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($customerQuery) use ($search) {
                foreach (['name', 'email', 'phone', 'address', 'group'] as $column) {
                    $customerQuery->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
            $query->whereRaw('LOWER(status) = ?', [strtolower($filters['status'])]);
        }

        return $query->paginate($perPage);
    }
    public function stats(): array
    {
        return [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::whereRaw('LOWER(status) = ?', ['active'])->count(),
            'inactive_customers' => Customer::whereRaw('LOWER(status) = ?', ['inactive'])->count(),
            'total_sales' => (float) Order::sum('grand_total'),
        ];
    }
    public function insertBulk(array $data): bool
    {
        return Customer::insert($data);
    }
    public function getForExport(array $filters = [])
    {
        $query = Customer::query();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($customerQuery) use ($search) {
                foreach (['name', 'email', 'phone', 'address', 'group'] as $column) {
                    $customerQuery->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
            $query->whereRaw('LOWER(status) = ?', [strtolower($filters['status'])]);
        }

        return $query->orderBy('id')->get([
            'name',
            'phone',
            'email',
            'address',
            'group',
            'status',
        ]);
    }
    public function existingEmails(array $emails): array
    {
        $existing = [];

        foreach (array_chunk($emails, 1000) as $emailChunk) {
            $existing = array_merge(
                $existing,
                Customer::query()
                    ->whereNotNull('email')
                    ->whereIn(DB::raw('LOWER(email)'), $emailChunk)
                    ->pluck('email')
                    ->map(fn ($email) => strtolower($email))
                    ->all()
            );
        }

        return $existing;
    }
    public function findById(string $id)
    {
        return Customer::findOrFail($id);
    }
    public function delete(string $id)
    {
        $result= Customer::findOrFail($id);
        return $result->delete();
    }
}
