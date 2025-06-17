<?php

declare(strict_types=1);

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
        Schema::create('executions', function (Blueprint $table) {
            $table->id();
            $table->string('ref')->nullable();
            $table->string('filename');
            $table->string('version');
            $table->string('campaign');
            $table->string('platform', 50)->default('chromium');
            $table->string('database', 50)->default('mysql');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('duration')->nullable();
            $table->integer('suites')->nullable();
            $table->integer('tests')->nullable();
            $table->integer('skipped')->nullable();
            $table->integer('pending')->nullable();
            $table->integer('passes')->nullable();
            $table->integer('failures')->nullable();
            $table->integer('broken_since_last')->nullable();
            $table->integer('fixed_since_last')->nullable();
            $table->integer('equal_since_last')->nullable();
            $table->dateTime('insertion_start_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('executions');
    }
};
