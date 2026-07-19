@extends('layouts.admin')

@section('title', 'Appointments Report')

@section('content')
    <a href="{{ route('admin.reports.index') }}" class="text-sm underline">&larr; All Reports</a>
    <h1 class="font-display text-2xl mt-3 mb-6">Appointments</h1>

    @include('admin.reports._date-range-form')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                        <th class="p-4">Service</th>
                        <th class="p-4">Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($byService as $row)
                        <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                            <td class="p-4">{{ $row->service_name }}</td>
                            <td class="p-4">{{ $row->total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="p-8 text-center opacity-60">No bookings in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                        <th class="p-4">Status</th>
                        <th class="p-4">Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($byStatus as $row)
                        <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                            <td class="p-4 capitalize">{{ $row->status }}</td>
                            <td class="p-4">{{ $row->total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="p-8 text-center opacity-60">No bookings in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
