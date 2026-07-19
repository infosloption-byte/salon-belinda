<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AdminActivityLog::query()
            ->with('user')
            ->when($request->query('user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->query('action'), fn ($q, $action) => $q->where('action', $action))
            ->when($request->query('date_from'), fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->query('date_to'), fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(40)
            ->withQueryString();

        return view('admin.activity-log.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'actions' => AdminActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
