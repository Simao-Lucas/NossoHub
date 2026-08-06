<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Converte event_media de IDs Immich para arquivos locais.
 * No-op em bancos já criados com o schema local (path/disk/…).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('event_media') || ! Schema::hasColumn('event_media', 'immich_asset_id')) {
            return;
        }

        Schema::table('event_media', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'immich_asset_id']);
        });

        DB::table('event_media')->delete();

        Schema::table('event_media', function (Blueprint $table) {
            $table->dropColumn('immich_asset_id');
        });

        Schema::table('event_media', function (Blueprint $table) {
            $table->string('path')->after('event_id');
            $table->string('disk')->default('public')->after('path');
            $table->string('original_name')->nullable()->after('disk');
            $table->string('mime_type')->nullable()->after('original_name');
            $table->unsignedBigInteger('size')->nullable()->after('mime_type');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('event_media') || Schema::hasColumn('event_media', 'immich_asset_id')) {
            return;
        }

        if (! Schema::hasColumn('event_media', 'path')) {
            return;
        }

        Schema::table('event_media', function (Blueprint $table) {
            $table->dropColumn(['path', 'disk', 'original_name', 'mime_type', 'size']);
        });

        Schema::table('event_media', function (Blueprint $table) {
            $table->string('immich_asset_id')->after('event_id');
            $table->unique(['event_id', 'immich_asset_id']);
        });
    }
};
