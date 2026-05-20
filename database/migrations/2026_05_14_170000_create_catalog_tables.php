<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog schema for Momentec product feed.
 *
 * category_product strategy: LEAF-ONLY — each product is linked only to the deepest
 * category node parsed from the feed path (e.g. "Adult | FLEECE | BOTTOMS ASB").
 * Breadcrumbs walk parent_id on categories.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('path')->nullable()->index();
            $table->timestamps();
            $table->unique(['parent_id', 'slug']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('parent_sku')->unique();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('admin_description_locked')->default(false);
            $table->string('division')->nullable();
            $table->string('currency', 8)->default('USD');
            $table->string('variation_theme')->nullable();
            $table->date('launch_date')->nullable();
            $table->text('features')->nullable();
            $table->decimal('min_msrp', 12, 4)->nullable();
            $table->decimal('max_msrp', 12, 4)->nullable();
            $table->decimal('min_cost', 12, 4)->nullable();
            $table->decimal('max_cost', 12, 4)->nullable();
            $table->string('default_main_image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('item_sku')->unique();
            $table->string('upc_code')->nullable()->index();
            $table->string('gtin')->nullable()->index();
            $table->decimal('msrp', 12, 4)->nullable();
            $table->decimal('cost', 12, 4)->nullable();
            $table->string('currency', 8)->default('USD');
            $table->string('division')->nullable();
            $table->text('item_description')->nullable();
            $table->boolean('admin_variant_description_locked')->default(false);
            $table->string('main_image_url')->nullable();
            $table->string('other_image_url')->nullable();
            $table->string('swatch_image_url')->nullable();
            $table->string('size_chart_image_url')->nullable();
            $table->string('variation_theme')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->decimal('weight', 10, 4)->nullable();
            $table->string('weight_unit', 16)->nullable();
            $table->decimal('volume', 12, 4)->nullable();
            $table->string('volume_unit', 16)->nullable();
            $table->unsignedInteger('case_pack_qty')->nullable();
            $table->string('color_hex_value', 32)->nullable();
            $table->string('status', 32)->nullable();
            $table->text('product_video_url')->nullable();
            $table->string('ribbon')->nullable();
            $table->string('country_of_origin', 64)->nullable();
            $table->timestamps();
            $table->index(['product_id', 'color']);
            $table->index(['product_id', 'size']);
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'category_id']);
        });

        Schema::create('product_variant_display_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('dimension', 32);
            $table->string('display_type', 16);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->string('label')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'dimension']);
        });

        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->string('role', 32);
            $table->text('source_url');
            $table->string('disk', 32)->default('public');
            $table->string('path')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('file_hash', 64)->nullable()->index();
            $table->string('download_status', 24)->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_variant_id', 'role']);
        });

        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mode', 64);
            $table->string('status', 32)->default('pending')->index();
            $table->string('source_file')->nullable();
            $table->string('secondary_source_file')->nullable();
            $table->json('parameters')->nullable();
            $table->string('current_step')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->json('error_sample')->nullable();
            $table->string('log_path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('secondary_feed_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_run_id')->nullable()->constrained('sync_runs')->nullOnDelete();
            $table->string('source_filename');
            $table->unsignedInteger('row_number')->default(0);
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secondary_feed_rows');
        Schema::dropIfExists('images');
        Schema::dropIfExists('product_variant_display_options');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('sync_runs');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};
