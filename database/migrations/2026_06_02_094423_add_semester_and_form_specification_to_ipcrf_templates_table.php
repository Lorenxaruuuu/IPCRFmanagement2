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
        Schema::table('ipcrf_templates', function (Blueprint $table) {
            $table->string('semester')->default('1st')->after('description');
            $table->string('form_specification')->default('Target')->after('semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipcrf_templates', function (Blueprint $table) {
            $table->dropColumn(['semester', 'form_specification']);
        });
    }
};
