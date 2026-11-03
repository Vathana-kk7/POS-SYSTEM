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
        Schema::create('purchase_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases') ->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products') ->cascadeOnDelete();
            $table->integer('quantity');
            $table->unsignedInteger('received_quantity')->default(0);
            // Purchase price per unit at the time of ordering
            $table->decimal('cost_price', 12, 2);
            // Optional item-level discount and tax
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            // Line total after discount and tax
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_item');
    }
};
