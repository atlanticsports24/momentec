<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('iso_code_2', 2)->unique();
            $table->char('iso_code_3', 3)->nullable();
            $table->text('address_format')->nullable();
            $table->boolean('postcode_required')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 32);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->unique(['country_id', 'code']);
        });

        Schema::create('geo_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('geo_zone_zone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['geo_zone_id', 'country_id', 'zone_id']);
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code', 3)->unique();
            $table->string('symbol_left', 12)->nullable();
            $table->string('symbol_right', 12)->nullable();
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->decimal('value', 15, 8)->default(1);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 64)->unique();
            $table->string('color', 32)->default('#6b7280');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_core')->default(false);
            $table->timestamps();
        });

        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->boolean('is_enabled')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('geo_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('min_total', 15, 4)->nullable();
            $table->decimal('max_total', 15, 4)->nullable();
            $table->foreignId('success_order_status_id')->nullable()->constrained('order_statuses')->nullOnDelete();
            $table->foreignId('failed_order_status_id')->nullable()->constrained('order_statuses')->nullOnDelete();
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->boolean('is_enabled')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('geo_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('cost', 15, 4)->default(0);
            $table->decimal('free_shipping_min', 15, 4)->nullable();
            $table->decimal('min_total', 15, 4)->nullable();
            $table->decimal('max_total', 15, 4)->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('order_status_id')->constrained('order_statuses');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('currency_code', 3)->default('USD');
            $table->decimal('currency_value', 15, 8)->default(1);
            $table->string('customer_email')->nullable();
            $table->string('customer_firstname')->nullable();
            $table->string('customer_lastname')->nullable();
            $table->string('customer_telephone')->nullable();
            $table->string('payment_company')->nullable();
            $table->string('payment_firstname')->nullable();
            $table->string('payment_lastname')->nullable();
            $table->string('payment_address_1')->nullable();
            $table->string('payment_address_2')->nullable();
            $table->string('payment_city')->nullable();
            $table->string('payment_postcode')->nullable();
            $table->foreignId('payment_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('payment_zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('shipping_firstname')->nullable();
            $table->string('shipping_lastname')->nullable();
            $table->string('shipping_address_1')->nullable();
            $table->string('shipping_address_2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_postcode')->nullable();
            $table->foreignId('shipping_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('shipping_zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('payment_method_code')->nullable();
            $table->string('payment_method_name')->nullable();
            $table->string('shipping_method_code')->nullable();
            $table->string('shipping_method_name')->nullable();
            $table->text('comment')->nullable();
            $table->decimal('subtotal', 15, 4)->default(0);
            $table->decimal('shipping_total', 15, 4)->default(0);
            $table->decimal('tax_total', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('name');
            $table->string('item_sku')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 15, 4);
            $table->decimal('total', 15, 4);
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('order_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('title');
            $table->decimal('value', 15, 4);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_status_id')->constrained('order_statuses');
            $table->boolean('notify')->default(false);
            $table->text('comment')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_histories');
        Schema::dropIfExists('order_totals');
        Schema::dropIfExists('order_products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('order_statuses');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('geo_zone_zone');
        Schema::dropIfExists('geo_zones');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('countries');
    }
};
