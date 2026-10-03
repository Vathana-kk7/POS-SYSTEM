<?php
namespace App\Repositories;

use App\Models\Supplier;
use App\Models\Purchase;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function create(array $data){
        return Supplier::create($data);
    }
    public function all(int $perPage = 10, array $filters = [])
    {
        $query = Supplier::query()
            ->withSum('purchases as total_payable', 'total')
            ->orderByDesc('id');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($supplierQuery) use ($search) {
                foreach (['name', 'contact_person', 'email', 'address', 'phone'] as $column) {
                    $supplierQuery->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
            $query->whereRaw('LOWER(status) = ?', [strtolower($filters['status'])]);
        }

        return $query->paginate($perPage);
    }
    public function findById(string $id){
        return  Supplier::findOrFail($id);
    }
    public function update(string $id,array $data){
        $supplier=Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier->refresh();
    }
    public function delete(string $id){
        $delete=Supplier::findOrFail($id);
        return $delete->delete();
    }
    public function stats(): array
    {
        return [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::whereRaw('LOWER(status) = ?', ['active'])->count(),
            'inactive_suppliers' => Supplier::whereRaw('LOWER(status) = ?', ['inactive'])->count(),
            'total_payable' => (float) Purchase::sum('total'),
        ];
    }
    public function insertBulk(array $data): bool
    {
        return Supplier::insert($data);
    }
    public function getForExport(array $filters = [])
    {
        return $this->supplierExportQuery($filters)->get([
            'id',
            'name',
            'contact_person',
            'email',
            'address',
            'phone',
            'status',
        ]);
    }

    public function getForPdfExport(array $filters = [])
    {
        return $this->supplierExportQuery($filters);
    }

    private function supplierExportQuery(array $filters)
    {
        $query = Supplier::query()->withSum('purchases as total_payable', 'total');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($supplierQuery) use ($search) {
                foreach (['name', 'contact_person', 'email', 'address', 'phone'] as $column) {
                    $supplierQuery->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
            $query->whereRaw('LOWER(status) = ?', [strtolower($filters['status'])]);
        }

        return $query->orderBy('id');
    }
    public function existingEmails(array $emails): array
    {
        $existing = [];
        foreach (array_chunk($emails, 1000) as $chunk) {
            $existing = array_merge($existing, Supplier::query()
                ->whereNotNull('email')
                ->whereIn(DB::raw('LOWER(email)'), $chunk)
                ->pluck('email')
                ->map(fn ($email) => strtolower($email))
                ->all());
        }

        return $existing;
    }

}
