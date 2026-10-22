<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('order_number', 50)->unique();
            // Zorunlu Tasarım Kuralı 2: Sipariş anındaki adres JSON olarak kopyalanır.
            $table->json('address');
            // Zorunlu Tasarım Kuralı 1: Fiyatlar her zaman decimal(10,2)
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('shipping', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->enum('payment_status', ['beklemede', 'odendi', 'basarisiz'])->default('beklemede');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
