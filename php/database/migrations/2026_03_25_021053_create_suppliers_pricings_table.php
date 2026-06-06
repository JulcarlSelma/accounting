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
        Schema::create('suppliers_pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('supplier_id');
            $table->decimal('price', 10, 2)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // FOREIGN KEY
            $table->foreign('product_id')->references('id')->on('products')->onDelete('RESTRICT');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('RESTRICT');
            // INDEXES
            $table->index(['product_id', 'supplier_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers_pricings');
    }
};
