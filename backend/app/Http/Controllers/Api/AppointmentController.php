<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = Service::find($data['service_id']);

        $appointment = Appointment::create([
            ...$data,
            'service_name' => $service?->name,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => "Thank you, {$appointment->name}. Your request has been received and the salon will call you to confirm.",
            'appointment' => $appointment,
        ], 201);
    }
}
