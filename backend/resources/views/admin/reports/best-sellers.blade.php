@extends('layouts.admin')

@section('title', 'Best Sellers')

@section('content')
    <a href="{{ route('admin.reports.index') }}" class="text-sm underline">&larr; All Reports</a>
    <h1 class="font-display text-2xl mt-3 mb-6">Best Sellers</h1>

    @include('admin.reports._date-range-form')

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">#</th>
                    <th class="p-4">Product</th>
                    <th class="p-4">Units Sold</th>
                    <th class="p-4">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bestSellers as $i => $row)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">{{ $i + 1 }}</td>
                        <td class="p-4">{{ $row->product_name }}</td>
                        <td class="p-4">{{ $row->units_sold }}</td>
                        <td class="p-4">LKR {{ number_format($row->revenue) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center opacity-60">No sales in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
