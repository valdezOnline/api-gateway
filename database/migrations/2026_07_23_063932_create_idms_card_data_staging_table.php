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
        Schema::create('idms_card_data_staging', function (Blueprint $table) {
            $table->id();
            $table->string('net_id')->default('');
            $table->string('ucr_id')->default('');
            $table->string('student_id')->default('');
            $table->string('iso')->default('');
            $table->string('library_card_number')->default('');
            $table->string('status1')->default('');
            $table->string('class')->default('');
            $table->string('yearr_in_school')->default('');
            $table->string('student_fac')->default('');
            $table->string('prox_int')->default('');
            $table->date('issue_date')->nullable()->default(null);
            $table->date('edit_date')->nullable()->default(null);
            $table->date('photo_date')->nullable()->default(null);
            $table->date('imported_date')->nullable()->default(null);
            $table->string('load_status')->nullable()->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idms_card_data_staging');
    }
};
