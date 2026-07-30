<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->nullable()->unique()->after('merchant_oid');
            $table->string('provider_transaction_id', 128)->nullable()->index()->after('payment_type');
            $table->string('authorization_code', 64)->nullable()->after('provider_transaction_id');
            $table->string('host_reference', 128)->nullable()->after('authorization_code');
            $table->string('response_code', 32)->nullable()->after('host_reference');
            $table->text('response_message')->nullable()->after('response_code');
            $table->string('three_d_status', 16)->nullable()->after('response_message');
            $table->string('masked_card', 32)->nullable()->after('three_d_status');
            $table->string('card_brand', 32)->nullable()->after('masked_card');
            $table->timestamp('redirected_at')->nullable()->after('notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'idempotency_key',
                'provider_transaction_id',
                'authorization_code',
                'host_reference',
                'response_code',
                'response_message',
                'three_d_status',
                'masked_card',
                'card_brand',
                'redirected_at',
            ]);
        });
    }
};
