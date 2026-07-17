@extends('layouts.admin')

@section('title', 'Appointments')

@section('content')
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach (['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
            <a href="{{ route('admin.appointments.index', array_filter(['status' => $value])) }}"
               class="btn {{ request('status', '') === $value ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Client</th>
                    <th class="p-4">Service</th>
                    <th class="p-4">Date &amp; Time</th>
                    <th class="p-4">Contact</th>
                    <th class="p-4">Status</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $a)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">{{ $a->name }}</td>
                        <td class="p-4">{{ $a->service_name }}</td>
                        <td class="p-4">{{ $a->date->format('d M Y') }} · {{ $a->time }}</td>
                        <td class="p-4">
                            {{ $a->phone }}
                            @if ($a->email)<br><span class="opacity-60">{{ $a->email }}</span>@endif
                        </td>
                        <td class="p-4">
                            <form method="POST" action="{{ route('admin.appointments.status', $a) }}">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="border px-2 py-1 text-xs" style="border-color: rgba(38,34,32,0.2);">
                                    @foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status)
                                        <option value="{{ $status }}" @selected($a->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="p-4">
                            <form method="POST" action="{{ route('admin.appointments.destroy', $a) }}" onsubmit="return confirm('Delete this appointment?')">
                                @csrf @method('DELETE')
                                <button class="text-xs" style="color:#7A2E3A;">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center opacity-60">No appointments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $appointments->links() }}</div>
@endsection
