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
        Schema::table('expert_rules', function (Blueprint $table) {
            $table->text('mitigation_recommendation')->nullable()->after('conclusion_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expert_rules', function (Blueprint $table) {
            $table->dropColumn('mitigation_recommendation');
        });
    }
};
