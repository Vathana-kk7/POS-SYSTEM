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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("email")->nullable(); // បន្ថែម Email
            $table->string("phone");
            $table->string("address")->nullable();
            $table->string("group")->default('Standard'); // បន្ថែម Group
            $table->string("status")->default('Active'); // បន្ថែម Status (Active / Inactive)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
