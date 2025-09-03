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
        // Rename the table from ucr_card_data to ucr_card_data_staging
        Schema::rename('ucr_card_data', 'ucr_card_data_staging');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        // Rename the table back to ucr_card_data
        Schema::rename('ucr_card_data_staging', 'ucr_card_data');
    }
};
