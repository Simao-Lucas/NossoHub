<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plan_categories')) {
            Schema::create('plan_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        $defaults = [
            ['name' => 'Restaurante', 'slug' => 'restaurant'],
            ['name' => 'Viagem', 'slug' => 'travel'],
            ['name' => 'Filme', 'slug' => 'movie'],
            ['name' => 'Presente', 'slug' => 'gift'],
            ['name' => 'Experiência', 'slug' => 'experience'],
        ];

        $sort = 1;
        foreach ($defaults as $default) {
            if (! DB::table('plan_categories')->where('slug', $default['slug'])->exists()) {
                DB::table('plan_categories')->insert([
                    'name' => $default['name'],
                    'slug' => $default['slug'],
                    'sort_order' => $sort,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $sort++;
        }

        if (! Schema::hasColumn('plan_items', 'plan_category_id')) {
            Schema::table('plan_items', function (Blueprint $table) {
                $table->unsignedBigInteger('plan_category_id')->nullable()->after('description');
            });

            Schema::table('plan_items', function (Blueprint $table) {
                $table->foreign('plan_category_id')
                    ->references('id')
                    ->on('plan_categories')
                    ->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('plan_items', 'category')) {
            $categories = DB::table('plan_categories')->pluck('id', 'slug');
            $fallbackId = $categories['experience'] ?? $categories->first();

            foreach (DB::table('plan_items')->orderBy('id')->get() as $item) {
                $slug = (string) ($item->category ?? '');
                DB::table('plan_items')->where('id', $item->id)->update([
                    'plan_category_id' => $categories[$slug] ?? $fallbackId,
                ]);
            }

            Schema::table('plan_items', function (Blueprint $table) {
                $table->dropIndex(['category', 'status']);
                $table->dropColumn('category');
            });

            Schema::table('plan_items', function (Blueprint $table) {
                $table->index(['plan_category_id', 'status']);
            });
        }

        DB::table('plan_items')->whereNull('plan_category_id')->update([
            'plan_category_id' => DB::table('plan_categories')->where('slug', 'experience')->value('id')
                ?? DB::table('plan_categories')->orderBy('id')->value('id'),
        ]);

        // Torna obrigatório sem doctrine/dbal
        DB::statement('ALTER TABLE plan_items MODIFY plan_category_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('plan_items', 'category') && Schema::hasColumn('plan_items', 'plan_category_id')) {
            Schema::table('plan_items', function (Blueprint $table) {
                $table->string('category')->nullable()->after('description');
            });

            $categories = DB::table('plan_categories')->pluck('slug', 'id');

            foreach (DB::table('plan_items')->orderBy('id')->get() as $item) {
                DB::table('plan_items')->where('id', $item->id)->update([
                    'category' => $categories[$item->plan_category_id] ?? 'experience',
                ]);
            }

            Schema::table('plan_items', function (Blueprint $table) {
                $table->dropForeign(['plan_category_id']);
                $table->dropIndex(['plan_category_id', 'status']);
                $table->dropColumn('plan_category_id');
                $table->index(['category', 'status']);
            });
        }

        Schema::dropIfExists('plan_categories');
    }
};
