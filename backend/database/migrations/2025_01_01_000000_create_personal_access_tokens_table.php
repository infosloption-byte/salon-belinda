<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Standard Laravel Sanctum migration (matches what
// `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
// generates). Hand-added here since this sandbox can't reach packagist.org to
// actually install the package — `composer require laravel/sanctum` will pick
// up this file already existing. Dated early (2025_01_01) so it always runs
// before any Sanctum-dependent migrations.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
