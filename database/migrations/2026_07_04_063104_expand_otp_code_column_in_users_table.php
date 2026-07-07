<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Perbesar kolom otp_code dari VARCHAR(6) menjadi VARCHAR(255)
     * agar dapat menampung bcrypt hash dari Hash::make().
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp_code', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp_code', 6)->nullable()->change();
        });
    }
};
