<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            // Yalnızca satın alan yazabilir kuralı için:
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->tinyInteger('rating'); // 1 - 5 yıldız
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false); // Admin onayı
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
