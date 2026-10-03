<table>
    <thead>
        <tr>
            <th width="5%">No.</th>
            <th width="15%">Supplier Name</th>
            <th width="14%">Contact Person</th>
            <th width="17%">Email</th>
            <th width="12%">Phone</th>
            <th width="19%">Address</th>
            <th width="8%">Status</th>
            <th width="10%">Total Payable</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($suppliers as $supplier)
            <tr>
                <td style="text-align: center;">{{ $startNumber + $loop->iteration }}</td>
                <td>{{ $supplier->name }}</td>
                <td>{{ $supplier->contact_person ?: '-' }}</td>
                <td>{{ $supplier->email ?: '-' }}</td>
                <td>{{ $supplier->phone }}</td>
                <td>{{ $supplier->address ?: '-' }}</td>
                <td>{{ $supplier->status }}</td>
                <td>{{ number_format((float) $supplier->total_payable, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align: center;">No suppliers found.</td></tr>
        @endforelse
    </tbody>
</table>
