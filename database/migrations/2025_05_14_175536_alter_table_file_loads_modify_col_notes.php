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
        // Modify the notes column in the file_loads table
        Schema::table('file_loads', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the notes column in the file_loads table to string
        Schema::table('file_loads', function (Blueprint $table) {
            $table->string('notes')->nullable()->change();
        });
    }
};
