<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->default('paytr');
            $table->string('merchant_oid', 64)->unique();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('email', 100);
            $table->unsignedInteger('payment_amount');
            $table->unsignedInteger('total_amount')->nullable();
            $table->string('currency', 8)->default('TL');
            $table->string('status', 32)->default('pending')->index();
            $table->string('iframe_token')->nullable();
            $table->string('payment_type', 32)->nullable();
            $table->unsignedTinyInteger('installment_count')->nullable();
            $table->string('failed_reason_code', 32)->nullable();
            $table->text('failed_reason_msg')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('provider_response')->nullable();
            $table->json('callback_payload')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index(['provider', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
