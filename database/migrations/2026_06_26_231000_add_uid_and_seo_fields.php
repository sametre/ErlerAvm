<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('seo_title', 180)->nullable()->after('description');
            $table->string('seo_description', 320)->nullable()->after('seo_title');
            $table->string('seo_keywords', 500)->nullable()->after('seo_description');
        });

        Schema::table('visitor_sessions', function (Blueprint $table): void {
            $table->string('visitor_uid_hash', 128)->nullable()->after('session_hash')->index();
        });
    }

    public function down(): void
    {
        Schema::table('visitor_sessions', function (Blueprint $table): void {
            $table->dropIndex(['visitor_uid_hash']);
            $table->dropColumn('visitor_uid_hash');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['seo_title', 'seo_description', 'seo_keywords']);
        });
    }
};
