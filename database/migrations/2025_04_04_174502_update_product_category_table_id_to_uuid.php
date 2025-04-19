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
        Schema::table('product_categories', function (Blueprint $table) {
            //
            // Drop the existing primary key constraint
            $table->dropPrimary('product_categories_pkey');
            // Drop the existing id column
            $table->dropColumn('id');
            // Add a new UUID column
            $table->uuid('id')->primary();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uuid', function (Blueprint $table) {
            //
            // Drop the UUID column
            $table->dropColumn('id');
            // Add the old id column back
            $table->bigIncrements('id')->primary();
            // Recreate the primary key constraint
            $table->primary('id');
            // Recreate the old primary key constraint
            $table->primary('product_categories_pkey');
            // Recreate the old foreign key constraints
            $table->foreign('product_categories_pkey')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_categories_pkey')->references('id')->on('categories')->onDelete('cascade');
        });
    }
};
