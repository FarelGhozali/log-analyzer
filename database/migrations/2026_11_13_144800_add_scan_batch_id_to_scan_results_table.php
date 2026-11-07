<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scan_results', function (Blueprint $table): void {
            $table->foreignId('scan_batch_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('scan_results', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('scan_batch_id');
        });
    }
};
