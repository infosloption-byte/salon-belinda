<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Categories used to be a fixed PHP array, backed by an enum column.
     * Now that they're managed in the product_categories table, the column
     * just needs to accept any string the admin defines.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category', 100)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('category', ['Hair Care', 'Skin Care', 'Makeup', 'Bridal Accessories'])->change();
        });
    }
};
