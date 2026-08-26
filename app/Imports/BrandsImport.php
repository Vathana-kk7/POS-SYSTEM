<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class BrandsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // ទុកទទេ ព្រោះយើងទាញ Collection ទៅរៀបចំក្នុង Service
    }
}
