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
        $this->expandShipmentProviders();
        $this->expandShipments();
        $this->expandShipmentMovements();
        $this->expandProductReviews();
        $this->expandStoreSettings();
        $this->expandBlogPosts();
        $this->expandMarketingTables();
    }

    public function down(): void
    {
        $this->dropColumnsIfExist('marketing_campaigns', [
            'public_id', 'name', 'channel', 'status', 'target_url', 'budget', 'starts_at', 'ends_at', 'payload',
        ]);

        $this->dropColumnsIfExist('segments', [
            'public_id', 'name', 'description', 'status', 'filters',
        ]);

        $this->dropColumnsIfExist('coupons', [
            'public_id', 'code', 'description', 'type', 'value', 'status', 'usage_limit', 'used_count', 'starts_at', 'ends_at',
        ]);

        $this->dropColumnsIfExist('blog_posts', [
            'cover_image_path', 'seo_title', 'seo_description', 'approved_by_id',
        ]);

        $this->dropColumnsIfExist('store_settings', [
            'public_id', 'group', 'key', 'value', 'is_public', 'updated_by_id',
        ]);

        $this->dropColumnsIfExist('product_reviews', [
            'admin_reply', 'answered_at', 'answered_by_id',
        ]);

        $this->dropColumnsIfExist('shipment_movements', [
            'public_id', 'shipment_id', 'user_id', 'status', 'note', 'payload',
        ]);

        $this->dropColumnsIfExist('shipments', [
            'public_id', 'order_id', 'shipment_provider_id', 'status', 'tracking_number', 'tracking_url',
            'customer_notified_at', 'shipped_at', 'delivered_at', 'admin_note', 'created_by_id',
        ]);

        $this->dropColumnsIfExist('shipment_providers', [
            'public_id', 'name', 'code', 'status', 'tracking_url_template', 'integration_mode', 'sort_order', 'settings',
        ]);
    }

    private function expandShipmentProviders(): void
    {
        Schema::table('shipment_providers', function (Blueprint $table): void {
            if (! Schema::hasColumn('shipment_providers', 'public_id')) {
                $table->ulid('public_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('shipment_providers', 'name')) {
                $table->string('name')->nullable()->after('public_id');
            }

            if (! Schema::hasColumn('shipment_providers', 'code')) {
                $table->string('code', 80)->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('shipment_providers', 'status')) {
                $table->string('status', 24)->default('active')->index()->after('code');
            }

            if (! Schema::hasColumn('shipment_providers', 'tracking_url_template')) {
                $table->string('tracking_url_template', 500)->nullable()->after('status');
            }

            if (! Schema::hasColumn('shipment_providers', 'integration_mode')) {
                $table->string('integration_mode', 24)->default('manual')->after('tracking_url_template');
            }

            if (! Schema::hasColumn('shipment_providers', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->index()->after('integration_mode');
            }

            if (! Schema::hasColumn('shipment_providers', 'settings')) {
                $table->json('settings')->nullable()->after('sort_order');
            }
        });

        $this->backfillPublicIds('shipment_providers');
    }

    private function expandShipments(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            if (! Schema::hasColumn('shipments', 'public_id')) {
                $table->ulid('public_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('shipments', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('public_id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('shipments', 'shipment_provider_id')) {
                $table->foreignId('shipment_provider_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('shipments', 'status')) {
                $table->string('status', 32)->default('preparing')->index()->after('shipment_provider_id');
            }

            if (! Schema::hasColumn('shipments', 'tracking_number')) {
                $table->string('tracking_number', 120)->nullable()->index()->after('status');
            }

            if (! Schema::hasColumn('shipments', 'tracking_url')) {
                $table->string('tracking_url', 600)->nullable()->after('tracking_number');
            }

            if (! Schema::hasColumn('shipments', 'customer_notified_at')) {
                $table->timestamp('customer_notified_at')->nullable()->index()->after('tracking_url');
            }

            if (! Schema::hasColumn('shipments', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->index()->after('customer_notified_at');
            }

            if (! Schema::hasColumn('shipments', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->index()->after('shipped_at');
            }

            if (! Schema::hasColumn('shipments', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('delivered_at');
            }

            if (! Schema::hasColumn('shipments', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->after('admin_note')->constrained('users');
            }
        });

        $this->backfillPublicIds('shipments');
    }

    private function expandShipmentMovements(): void
    {
        Schema::table('shipment_movements', function (Blueprint $table): void {
            if (! Schema::hasColumn('shipment_movements', 'public_id')) {
                $table->ulid('public_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('shipment_movements', 'shipment_id')) {
                $table->foreignId('shipment_id')->nullable()->after('public_id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('shipment_movements', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('shipment_id')->constrained();
            }

            if (! Schema::hasColumn('shipment_movements', 'status')) {
                $table->string('status', 32)->index()->after('user_id');
            }

            if (! Schema::hasColumn('shipment_movements', 'note')) {
                $table->text('note')->nullable()->after('status');
            }

            if (! Schema::hasColumn('shipment_movements', 'payload')) {
                $table->json('payload')->nullable()->after('note');
            }
        });

        $this->backfillPublicIds('shipment_movements');
    }

    private function expandProductReviews(): void
    {
        Schema::table('product_reviews', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_reviews', 'admin_reply')) {
                $table->text('admin_reply')->nullable()->after('comment');
            }

            if (! Schema::hasColumn('product_reviews', 'answered_at')) {
                $table->timestamp('answered_at')->nullable()->index()->after('status');
            }

            if (! Schema::hasColumn('product_reviews', 'answered_by_id')) {
                $table->foreignId('answered_by_id')->nullable()->after('answered_at')->constrained('users');
            }
        });
    }

    private function expandStoreSettings(): void
    {
        Schema::table('store_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('store_settings', 'public_id')) {
                $table->ulid('public_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('store_settings', 'group')) {
                $table->string('group', 80)->default('general')->index()->after('public_id');
            }

            if (! Schema::hasColumn('store_settings', 'key')) {
                $table->string('key', 120)->unique()->after('group');
            }

            if (! Schema::hasColumn('store_settings', 'value')) {
                $table->json('value')->nullable()->after('key');
            }

            if (! Schema::hasColumn('store_settings', 'is_public')) {
                $table->boolean('is_public')->default(false)->index()->after('value');
            }

            if (! Schema::hasColumn('store_settings', 'updated_by_id')) {
                $table->foreignId('updated_by_id')->nullable()->after('is_public')->constrained('users');
            }
        });

        $this->backfillPublicIds('store_settings');
    }

    private function expandBlogPosts(): void
    {
        Schema::table('blog_posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('blog_posts', 'cover_image_path')) {
                $table->string('cover_image_path', 500)->nullable()->after('excerpt');
            }

            if (! Schema::hasColumn('blog_posts', 'seo_title')) {
                $table->string('seo_title', 180)->nullable()->after('content');
            }

            if (! Schema::hasColumn('blog_posts', 'seo_description')) {
                $table->string('seo_description', 320)->nullable()->after('seo_title');
            }

            if (! Schema::hasColumn('blog_posts', 'approved_by_id')) {
                $table->foreignId('approved_by_id')->nullable()->after('published_at')->constrained('users');
            }
        });
    }

    private function expandMarketingTables(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table): void {
            if (! Schema::hasColumn('marketing_campaigns', 'public_id')) {
                $table->ulid('public_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('marketing_campaigns', 'name')) {
                $table->string('name')->nullable()->after('public_id');
            }

            if (! Schema::hasColumn('marketing_campaigns', 'channel')) {
                $table->string('channel', 40)->default('site')->index()->after('name');
            }

            if (! Schema::hasColumn('marketing_campaigns', 'status')) {
                $table->string('status', 24)->default('draft')->index()->after('channel');
            }

            if (! Schema::hasColumn('marketing_campaigns', 'target_url')) {
                $table->string('target_url', 500)->nullable()->after('status');
            }

            if (! Schema::hasColumn('marketing_campaigns', 'budget')) {
                $table->decimal('budget', 12, 2)->nullable()->after('target_url');
            }

            if (! Schema::hasColumn('marketing_campaigns', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->index()->after('budget');
            }

            if (! Schema::hasColumn('marketing_campaigns', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->index()->after('starts_at');
            }

            if (! Schema::hasColumn('marketing_campaigns', 'payload')) {
                $table->json('payload')->nullable()->after('ends_at');
            }
        });

        Schema::table('segments', function (Blueprint $table): void {
            if (! Schema::hasColumn('segments', 'public_id')) {
                $table->ulid('public_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('segments', 'name')) {
                $table->string('name')->nullable()->after('public_id');
            }

            if (! Schema::hasColumn('segments', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('segments', 'status')) {
                $table->string('status', 24)->default('active')->index()->after('description');
            }

            if (! Schema::hasColumn('segments', 'filters')) {
                $table->json('filters')->nullable()->after('status');
            }
        });

        Schema::table('coupons', function (Blueprint $table): void {
            if (! Schema::hasColumn('coupons', 'public_id')) {
                $table->ulid('public_id')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('coupons', 'code')) {
                $table->string('code', 80)->nullable()->unique()->after('public_id');
            }

            if (! Schema::hasColumn('coupons', 'description')) {
                $table->text('description')->nullable()->after('code');
            }

            if (! Schema::hasColumn('coupons', 'type')) {
                $table->string('type', 24)->default('fixed')->after('description');
            }

            if (! Schema::hasColumn('coupons', 'value')) {
                $table->decimal('value', 12, 2)->default(0)->after('type');
            }

            if (! Schema::hasColumn('coupons', 'status')) {
                $table->string('status', 24)->default('draft')->index()->after('value');
            }

            if (! Schema::hasColumn('coupons', 'usage_limit')) {
                $table->unsignedInteger('usage_limit')->nullable()->after('status');
            }

            if (! Schema::hasColumn('coupons', 'used_count')) {
                $table->unsignedInteger('used_count')->default(0)->after('usage_limit');
            }

            if (! Schema::hasColumn('coupons', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->index()->after('used_count');
            }

            if (! Schema::hasColumn('coupons', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->index()->after('starts_at');
            }
        });

        $this->backfillPublicIds('marketing_campaigns');
        $this->backfillPublicIds('segments');
        $this->backfillPublicIds('coupons');
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropColumnsIfExist(string $table, array $columns): void
    {
        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($table, $column),
        ));

        if ($existing === []) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }

    private function backfillPublicIds(string $table): void
    {
        if (! Schema::hasColumn($table, 'public_id')) {
            return;
        }

        DB::table($table)
            ->whereNull('public_id')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $row) use ($table): void {
                DB::table($table)
                    ->where('id', $row->id)
                    ->update(['public_id' => (string) Str::ulid()]);
            });
    }
};
