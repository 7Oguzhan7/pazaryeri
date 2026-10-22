<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('color', 50)->nullable();
            $table->string('size', 50)->nullable();
            // Zorunlu Tasarım Kuralı 1: Fiyat her zaman decimal(10,2) olarak saklanır, asla float olmaz.
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            // Zorunlu Tasarım Kuralı 3: Stok product_variants tablosundadır ve lockForUpdate() ile düşülür.
            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
