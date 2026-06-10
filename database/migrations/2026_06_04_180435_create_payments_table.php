<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('bank_code', ['bca', 'bni', 'bri', 'mandiri', 'permata']);
            $table->string('virtual_account_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['waiting_payment', 'paid', 'expired', 'failed'])->default('waiting_payment');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('external_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
