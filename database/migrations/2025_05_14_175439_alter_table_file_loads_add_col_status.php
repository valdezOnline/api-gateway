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
        // Add the status column to the file_loads table
        Schema::table('file_loads', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('direction');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the status column from the file_loads table
        Schema::table('file_loads', function (Blueprint $table) {
            $table->dropColumn('status');
        });

    }
};
