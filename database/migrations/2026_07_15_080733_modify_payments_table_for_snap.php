<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('bank_code')->nullable()->change();
            $table->string('virtual_account_number')->nullable()->change();
            $table->string('snap_token')->nullable()->after('external_reference');
            $table->string('snap_url')->nullable()->after('snap_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('bank_code')->nullable(false)->change();
            $table->string('virtual_account_number')->nullable(false)->change();
            $table->dropColumn(['snap_token', 'snap_url']);
        });
    }
};
