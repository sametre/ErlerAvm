<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 64)->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'created_at'], 'user_notifications_user_read_created_idx');
        });

        Schema::create('push_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 32)->index();
            $table->string('token', 512)->unique();
            $table->string('device_id', 128)->nullable()->index();
            $table->string('app_version', 64)->nullable();
            $table->string('locale', 16)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->boolean('enabled')->default(true)->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'platform', 'enabled'], 'push_tokens_user_platform_enabled_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_device_tokens');
        Schema::dropIfExists('user_notifications');
    }
};
