<?php

use App\Models\Brand;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->integer("stock_qty")->default(0);
            $table->decimal("cost_price",10,2);
            $table->text("description");
            $table->string("sku")->unique();
            $table->text("image")->nullable();
            $table->integer("min_stock_level")->default(0);
            $table->string("status");
            $table->decimal("selling_price",10,2);
            // កែសម្រួល៖ បន្ថែម ->nullable() មុន nullOnDelete()
            $table->foreignId("brand_id")->nullable()->constrained("brands")->nullOnDelete();
            $table->foreignId("category_id")->nullable()->constrained("categories")->nullOnDelete();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
