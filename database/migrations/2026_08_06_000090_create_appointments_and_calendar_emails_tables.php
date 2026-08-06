<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('summary');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('all_day')->default(false);
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->string('status')->default('confirmed'); // confirmed|tentative|cancelled
            $table->string('visibility')->default('default'); // default|public|private
            $table->string('transparency')->default('opaque'); // opaque|transparent
            $table->timestamps();

            $table->index(['starts_at', 'ends_at']);
            $table->index('status');
        });

        Schema::create('calendar_emails', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_emails');
        Schema::dropIfExists('appointments');
    }
};
