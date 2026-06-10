<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim')->nullable()->after('email');
            $table->boolean('push_notifications_enabled')->default(true)->after('is_active');
            $table->boolean('email_notifications_enabled')->default(true)->after('push_notifications_enabled');
            $table->boolean('biometric_login_enabled')->default(false)->after('email_notifications_enabled');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('original_price', 15, 2)->nullable()->after('price');
            $table->decimal('rating', 3, 1)->default(4.8)->after('original_price');
            $table->integer('reviews_count')->default(0)->after('rating');
            $table->json('sizes')->nullable()->after('reviews_count');
            $table->json('colors')->nullable()->after('sizes');
            $table->boolean('is_recommended')->default(false)->after('is_active');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('size')->nullable()->after('product_id');
            $table->string('color')->nullable()->after('size');

            // Drop foreign keys first to allow dropping the unique index
            $table->dropForeign(['cart_id']);
            $table->dropForeign(['product_id']);

            // Drop unique index
            $table->dropUnique(['cart_id', 'product_id']);

            // Add new unique index including size and color
            $table->unique(['cart_id', 'product_id', 'size', 'color']);

            // Re-add foreign keys
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('size')->nullable()->after('product_id');
            $table->string('color')->nullable()->after('size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['size', 'color']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['cart_id']);
            $table->dropForeign(['product_id']);

            $table->dropUnique(['cart_id', 'product_id', 'size', 'color']);
            $table->dropColumn(['size', 'color']);

            $table->unique(['cart_id', 'product_id']);

            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'rating', 'reviews_count', 'sizes', 'colors', 'is_recommended']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'push_notifications_enabled', 'email_notifications_enabled', 'biometric_login_enabled']);
        });
    }
};
