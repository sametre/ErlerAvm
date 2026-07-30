<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('order_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('product_public_id', 32);
            $table->string('name');
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'product_public_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
