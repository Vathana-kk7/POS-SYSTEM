<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bakong_account_id')->nullable();
            $table->string('khqr_image')->nullable();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'account_name',
                'account_number',
                'bakong_account_id',
                'khqr_image',
            ]);
        });
    }
};
