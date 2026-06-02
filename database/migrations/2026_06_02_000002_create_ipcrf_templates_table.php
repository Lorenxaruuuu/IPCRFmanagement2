<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipcrf_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_original_name');
            $table->json('sheet_data')->nullable();   // parsed cell data JSON
            $table->json('merged_cells')->nullable(); // merged cell ranges
            $table->integer('total_rows')->default(0);
            $table->integer('total_cols')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('template_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('ipcrf_templates')->onDelete('cascade');
            $table->foreignId('position_id')->constrained('positions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['template_id', 'position_id']);
        });

        Schema::create('template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('ipcrf_templates')->onDelete('cascade');
            $table->string('cell_ref');           // e.g. "C5"
            $table->integer('sheet_index')->default(0);
            $table->integer('row_index');
            $table->integer('col_index');
            $table->string('field_type');         // autofill_name|autofill_position|autofill_department|autofill_date|text|number|textarea|rating|dropdown|signature|readonly
            $table->string('field_label')->nullable();
            $table->json('field_options')->nullable(); // for dropdown: list of options
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_fields');
        Schema::dropIfExists('template_positions');
        Schema::dropIfExists('ipcrf_templates');
    }
};
