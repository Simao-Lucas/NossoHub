<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('occurred_at');
            $table->timestamps();

            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
