<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wishlist_items')) {
            return;
        }

        if (Schema::hasTable('plan_items')) {
            Schema::drop('plan_items');
        }

        Schema::rename('wishlist_items', 'plan_items');
    }

    public function down(): void
    {
        if (Schema::hasTable('plan_items') && ! Schema::hasTable('wishlist_items')) {
            Schema::rename('plan_items', 'wishlist_items');
        }
    }
};
