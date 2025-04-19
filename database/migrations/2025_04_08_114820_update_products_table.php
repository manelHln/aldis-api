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
        //
        Schema::table('products', function(Blueprint $table){
            $table->dropColumn('type');
            $table->foreignUuid('category_id')->constrained('product_categories', 'id');
            $table->foreignUuid('product_type_id')->constrained('product_types', 'id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('products', function(Blueprint $table){
            $table->string('type', 50);
            $table->dropColumn('category_id');
            $table->dropColumn('product_type_id');
        });
    }
};
