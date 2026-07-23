<?php

use Illuminate\Support\Facades\Route;

// The Blade admin panel has been fully cut over to the React admin
// (see ADMIN-MIGRATION-TASKS.md — Phase 2). All admin functionality now
// lives behind /api/admin/* (routes/api.php), consumed by the admin/ SPA.
// This file only needs to answer for infra health checks now.

Route::get('/', function () {
    return response()->json(['status' => 'ok', 'service' => 'salon-belinda-backend']);
});

Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
});
