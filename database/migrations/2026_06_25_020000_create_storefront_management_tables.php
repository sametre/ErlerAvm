<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_navigation_items', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('location', 32)->index();
            $table->string('title');
            $table->string('url');
            $table->string('status', 24)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->index(['location', 'status', 'sort_order']);
        });

        Schema::create('storefront_banners', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('placement', 32)->default('home_hero')->index();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status', 24)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamps();

            $table->index(['placement', 'status', 'sort_order']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('product_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->integer('quantity_delta');
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->string('reason', 64)->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('storefront_banners');
        Schema::dropIfExists('storefront_navigation_items');
    }
};
