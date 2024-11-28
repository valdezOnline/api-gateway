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
        Schema::create('ucr_card_data', function (Blueprint $table) {
            $table->id();
            $table->string('net_id');
            $table->string('ssn');
            $table->string('student_id');
            $table->string('iso');
            $table->string('lib_num');
            $table->string('status1');
            $table->string('status2');
            $table->string('class');
            $table->string('yr_in_school');
            $table->string('stud_fac');
            $table->string('prox_int');
            $table->string('prox_ext');
            $table->date('issued');
            $table->date('edit_date');
            $table->date('photo_date');
            $table->date('imported');
            $table->string('load_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucr_card_data');
    }
};
