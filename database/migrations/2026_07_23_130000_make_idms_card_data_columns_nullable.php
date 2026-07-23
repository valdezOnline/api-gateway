<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['idms_card_data_staging', 'idms_card_data'];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY match_key VARCHAR(191) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY net_id VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY ucr_id VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY student_id VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY iso VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY library_card_number VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY status1 VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY class VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY year_in_school VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY student_fac VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY prox_int VARCHAR(255) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY load_status VARCHAR(255) NULL DEFAULT NULL");

            DB::statement("ALTER TABLE {$table} MODIFY issue_date DATE NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY edit_date DATE NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY photo_date DATE NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY imported_date DATE NULL DEFAULT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['idms_card_data_staging', 'idms_card_data'];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY match_key VARCHAR(191) NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY net_id VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY ucr_id VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY student_id VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY iso VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY library_card_number VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY status1 VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY class VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY year_in_school VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY student_fac VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY prox_int VARCHAR(255) NOT NULL DEFAULT ''");
            DB::statement("ALTER TABLE {$table} MODIFY load_status VARCHAR(255) NOT NULL DEFAULT ''");

            DB::statement("ALTER TABLE {$table} MODIFY issue_date DATE NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY edit_date DATE NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY photo_date DATE NULL DEFAULT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY imported_date DATE NULL DEFAULT NULL");
        }
    }
};