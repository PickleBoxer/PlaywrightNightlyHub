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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suite_id')->constrained()->onDelete('cascade');
            $table->string('uuid');
            $table->string('title');
            $table->string('state')->nullable();
            $table->string('identifier')->nullable();
            $table->integer('duration')->default(0);
            $table->text('error_message')->nullable();
            $table->text('stack_trace')->nullable();
            $table->text('diff')->nullable();
            $table->dateTime('insertion_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
