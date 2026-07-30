<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->ulid('public_id')->nullable()->after('id');
        });

        // Backfill existing rows with ULIDs
        DB::table('product_reviews')->whereNull('public_id')->orderBy('id')->each(function ($review) {
            DB::table('product_reviews')
                ->where('id', $review->id)
                ->update(['public_id' => strtolower(Str::ulid())]);
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->ulid('public_id')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
};
