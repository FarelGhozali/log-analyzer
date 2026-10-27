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
        Schema::create('scan_results', function (Blueprint $table) {
            $table->id();
            $table->string('source_ip', 45)->index();
            $table->unsignedSmallInteger('target_port');
            $table->unsignedInteger('failed_attempts');
            $table->string('status')->index();
            $table->foreignId('triggered_rule_id')
                ->nullable()
                ->constrained('expert_rules')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_results');
    }
};
