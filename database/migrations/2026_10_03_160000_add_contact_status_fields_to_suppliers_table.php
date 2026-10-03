<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('name');
            }

            if (!Schema::hasColumn('suppliers', 'email')) {
                $table->string('email')->nullable()->unique()->after('contact_person');
            }

            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->string('status')->default('Active')->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'email')) {
                $table      ->dropUnique(['email']);
            }

            $columns = array_values(array_filter(
                ['contact_person', 'email', 'status'],
                fn (string $column): bool => Schema::hasColumn('suppliers', $column),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
