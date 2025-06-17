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
        Schema::create('suites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')->constrained()->onDelete('cascade');
            $table->string('uuid');
            $table->string('title');
            $table->boolean('has_skipped')->default(false);
            $table->boolean('has_pending')->default(false);
            $table->boolean('has_passes')->default(false);
            $table->boolean('has_failures')->default(false);
            $table->boolean('has_suites')->default(false);
            $table->boolean('has_tests')->default(false);
            $table->integer('total_skipped')->default(0);
            $table->integer('total_pending')->default(0);
            $table->integer('total_passes')->default(0);
            $table->integer('total_failures')->default(0);
            $table->integer('parent_id')->nullable();
            $table->string('campaign')->nullable();
            $table->string('file')->nullable();
            $table->integer('duration')->default(0);
            $table->dateTime('insertion_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suites');
    }
};
