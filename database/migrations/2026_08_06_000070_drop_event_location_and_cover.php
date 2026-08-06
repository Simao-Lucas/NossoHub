<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter(
            ['location', 'cover_immich_asset_id'],
            fn (string $column): bool => Schema::hasColumn('events', $column),
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('events', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('location')->nullable()->after('occurred_at');
            $table->string('cover_immich_asset_id')->nullable()->after('location');
        });
    }
};
