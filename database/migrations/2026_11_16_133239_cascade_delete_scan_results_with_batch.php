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
            $table->dropForeign(['scan_batch_id']);
            $table->foreign('scan_batch_id')
                ->references('id')
                ->on('scan_batches')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->dropForeign(['scan_batch_id']);
            $table->foreign('scan_batch_id')
                ->references('id')
                ->on('scan_batches')
                ->nullOnDelete();
        });
    }
};
