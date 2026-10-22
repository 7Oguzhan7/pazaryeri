<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->restrictOnDelete();
            $table->string('period', 20); // YYYY-MM
            $table->decimal('gross', 10, 2);      // Toplam brüt satış
            $table->decimal('commission', 10, 2); // Platform komisyon kesintisi
            $table->decimal('net', 10, 2);        // Satıcıya ödenecek net tutar
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_payouts');
    }
};
