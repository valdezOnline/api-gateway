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
        Schema::create('library_patrons', function (Blueprint $table) {
            $table->id();
            $table->string('primary_id')->default('');
            $table->string('first_name')->default('');
            $table->string('middle_name')->default('');
            $table->string('last_name')->default('');
            $table->string('record_type')->default('');
            $table->string('user_groupd')->default('');
            $table->string('user_group_desc')->default('');
            $table->string('account_type')->default('');
            $table->string('status')->default('');
            $table->string('expiry_date')->default('');
            $table->string('address_line1')->default('');
            $table->string('address_line2')->default('');
            $table->string('address_city')->default('');
            $table->string('address_state_province')->default('');
            $table->string('address_postal_code')->default('');
            $table->string('address_country')->default('');
            $table->string('email')->default('');
            $table->string('phone')->default('');
            $table->string('identifier_netid')->default('');
            $table->string('identifier_barcode')->default('');
            $table->string('identifier_netid_email')->default('');
            $table->string('notes')->default('');
            $table->string('source')->default('');
            $table->string('updated_by')->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_patrons');
    }
};
