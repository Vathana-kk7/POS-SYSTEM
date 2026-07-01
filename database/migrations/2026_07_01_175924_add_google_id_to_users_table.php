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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('google_id')->nullable()->after('email');
        $table->string('password')->nullable()->change(); // អនុញ្ញាតឱ្យ password ទៅជា null ព្រោះឡុកអុីនតាម Google មិនបាច់មាន password ទេ
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
