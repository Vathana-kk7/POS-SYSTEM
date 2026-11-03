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
       Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            // Purchase Order Information
            $table->string('purchase_order_no')->unique();
            $table->date('purchase_date');
            $table->date('expected_delivery_date')->nullable();
            // Supplier
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();
            // Staff who created the purchase order
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            // Financial Information
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            // Payment and Status
            $table->string('payment_terms')->nullable();
            $table->string('status')->default('draft');
            // Additional Information
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
