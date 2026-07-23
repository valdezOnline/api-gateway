<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('idms_card_data_staging', function (Blueprint $table) {
            $table->string('match_key', 191)->nullable()->after('prox_int');

            $table->index('match_key', 'idms_stage_match_key_idx');
            $table->index(['net_id', 'ucr_id', 'student_id'], 'idms_stage_tri_idx');
            $table->index(['net_id', 'ucr_id'], 'idms_stage_net_ucr_idx');
            $table->index(['net_id', 'student_id'], 'idms_stage_net_student_idx');
            $table->index(['ucr_id', 'student_id'], 'idms_stage_ucr_student_idx');
        });

        Schema::table('idms_card_data', function (Blueprint $table) {
            $table->string('match_key', 191)->nullable()->after('prox_int');

            $table->unique('match_key', 'idms_data_match_key_unique');
            $table->index(['net_id', 'ucr_id', 'student_id'], 'idms_data_tri_idx');
            $table->index(['net_id', 'ucr_id'], 'idms_data_net_ucr_idx');
            $table->index(['net_id', 'student_id'], 'idms_data_net_student_idx');
            $table->index(['ucr_id', 'student_id'], 'idms_data_ucr_student_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idms_card_data_staging', function (Blueprint $table) {
            $table->dropIndex('idms_stage_match_key_idx');
            $table->dropIndex('idms_stage_tri_idx');
            $table->dropIndex('idms_stage_net_ucr_idx');
            $table->dropIndex('idms_stage_net_student_idx');
            $table->dropIndex('idms_stage_ucr_student_idx');

            $table->dropColumn('match_key');
        });

        Schema::table('idms_card_data', function (Blueprint $table) {
            $table->dropUnique('idms_data_match_key_unique');
            $table->dropIndex('idms_data_tri_idx');
            $table->dropIndex('idms_data_net_ucr_idx');
            $table->dropIndex('idms_data_net_student_idx');
            $table->dropIndex('idms_data_ucr_student_idx');

            $table->dropColumn('match_key');
        });
    }
};
