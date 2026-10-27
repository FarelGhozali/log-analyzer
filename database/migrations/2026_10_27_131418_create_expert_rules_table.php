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
        Schema::create('expert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name');
            $table->unsignedSmallInteger('target_port');
            $table->unsignedInteger('min_failed_attempts');
            $table->string('threat_level');
            $table->string('conclusion_message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_rules');
    }
};
