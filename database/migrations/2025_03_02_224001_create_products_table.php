<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable(false)->unique(true);
            $table->integer('price', false, true)->nullable(false);
            $table->boolean('is_available')->default(true);
            $table->text('description')->nullable(false);
            $table->string('image_url')->nullable(false);
            $table->integer('stock')->nullable(false);
            $table->string('origin', 50)->nullable(false);
            $table->string('type', 50)->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
