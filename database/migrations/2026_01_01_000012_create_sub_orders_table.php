<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pazaryeri Mantığı: 1 Ana sipariş (orders) ve satıcı başına 1 alt sipariş (sub_orders)
        Schema::create('sub_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->restrictOnDelete();
            $table->string('sub_order_number', 60)->unique();
            $table->enum('status', [
                'onay_bekliyor',
                'hazirlaniyor',
                'kargoya_verildi',
                'teslim_edildi',
                'iptal',
                'iade'
            ])->default('onay_bekliyor');
            $table->string('cargo_company', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->decimal('commission_amount', 10, 2)->default(0.00); // Platform komisyonu
            $table->decimal('seller_amount', 10, 2)->default(0.00);     // Satıcı hakedişi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_orders');
    }
};
