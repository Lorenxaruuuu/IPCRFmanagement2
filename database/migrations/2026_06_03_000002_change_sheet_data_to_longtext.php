<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change sheet_data and merged_cells from JSON to LONGTEXT
        // to support very large spreadsheet data without MySQL packet issues
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('ipcrf_templates', function (Blueprint $table) {
                $table->longText('sheet_data')->nullable()->change();
                $table->longText('merged_cells')->nullable()->change();
            });
        } else {
            DB::statement('ALTER TABLE ipcrf_templates MODIFY sheet_data LONGTEXT NULL');
            DB::statement('ALTER TABLE ipcrf_templates MODIFY merged_cells LONGTEXT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('ipcrf_templates', function (Blueprint $table) {
                $table->json('sheet_data')->nullable()->change();
                $table->json('merged_cells')->nullable()->change();
            });
        } else {
            DB::statement('ALTER TABLE ipcrf_templates MODIFY sheet_data JSON NULL');
            DB::statement('ALTER TABLE ipcrf_templates MODIFY merged_cells JSON NULL');
        }
    }
};
