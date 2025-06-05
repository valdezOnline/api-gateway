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
        Schema::create('ucr_card_services_data', function (Blueprint $table) {
            $table->id();
            $table->string('net_id')->default('');
            $table->string('student_id')->default('');
            $table->string('employee_id')->default('');
            $table->string('iso')->default('');
            $table->string('lib_num')->default('');
            $table->date('issued')->nullable()->default(null);
            $table->string('status')->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_ucr_card_services_data');
    }
};
