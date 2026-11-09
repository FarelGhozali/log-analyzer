<?php

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
        Schema::table('scan_results', function (Blueprint $table) {
            $table->json('raw_log_snippets')->nullable()->after('failed_attempts');
            $table->dateTime('event_timestamp')->nullable()->after('raw_log_snippets')->index();
            $table->json('fact_snapshot')->nullable()->after('event_timestamp');
            $table->json('explanation')->nullable()->after('fact_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->dropIndex(['event_timestamp']);
            $table->dropColumn([
                'raw_log_snippets',
                'event_timestamp',
                'fact_snapshot',
                'explanation',
            ]);
        });
    }
};
