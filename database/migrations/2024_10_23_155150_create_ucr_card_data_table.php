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
            $table->string('net_id')->default('');
            $table->string('ssn')->default('');
            $table->string('student_id')->default('');
            $table->string('iso')->default('');
            $table->string('lib_num')->default('');
            $table->string('status1')->default('');
            // $table->string('status2')->default('');
            $table->string('class')->default('');
            $table->string('yr_in_school')->default('');
            $table->string('stud_fac')->default('');
            $table->string('prox_int')->default('');
            $table->string('prox_ext')->default('');
            $table->string('issued')->default('');
            $table->string('edit_date')->default('');
            $table->string('photo_date')->default('');
            $table->string('imported')->default('');
            $table->string('load_status')->default('');
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
