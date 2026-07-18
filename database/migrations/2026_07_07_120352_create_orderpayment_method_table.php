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
        Schema::create('orderpayment_method', function (Blueprint $table) {
            $table->id();
            $table->decimal("amount",10,2);
            $table->string("transaction_id")->nullable();
            $table->string("payment_status");
            $table->timestamp("paid_at")->nullable();
            $table->foreignId("order_id")->constrained("order")->restrictOnDelete();
            $table->foreignId("bank_id")->constrained("bank")->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orderpayment_method');
    }
};
