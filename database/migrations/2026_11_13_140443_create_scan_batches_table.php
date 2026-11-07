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
        Schema::create('scan_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('log_file_name');
            $table->unsignedInteger('total_results');
            $table->unsignedInteger('attack_count');
            $table->unsignedInteger('safe_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_batches');
    }
};
