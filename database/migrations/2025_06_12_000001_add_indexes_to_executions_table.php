<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('executions', function (Blueprint $table) {
            // Add indexes for better query performance
            $table->index(['start_date'], 'executions_start_date_index');
            $table->index(['platform'], 'executions_platform_index');
            $table->index(['campaign'], 'executions_campaign_index');
            $table->index(['version'], 'executions_version_index');
            $table->index(['platform', 'campaign'], 'executions_platform_campaign_index');
            $table->index(['start_date', 'platform'], 'executions_date_platform_index');

            // For filename searches
            $table->index(['filename'], 'executions_filename_index');
        });
    }

    public function down(): void
    {
        Schema::table('executions', function (Blueprint $table) {
            $table->dropIndex('executions_start_date_index');
            $table->dropIndex('executions_platform_index');
            $table->dropIndex('executions_campaign_index');
            $table->dropIndex('executions_version_index');
            $table->dropIndex('executions_platform_campaign_index');
            $table->dropIndex('executions_date_platform_index');
            $table->dropIndex('executions_filename_index');
        });
    }
};
