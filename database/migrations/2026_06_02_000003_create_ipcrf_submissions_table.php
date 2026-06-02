<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipcrf_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('ipcrf_templates')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('draft'); // draft|submitted|under_review|approved|rejected
            $table->text('admin_remarks')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('generated_file_path')->nullable(); // path to generated XLSX
            $table->timestamps();
        });

        Schema::create('submission_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('ipcrf_submissions')->onDelete('cascade');
            $table->foreignId('template_field_id')->constrained('template_fields')->onDelete('cascade');
            $table->longText('value')->nullable();
            $table->timestamps();
            $table->unique(['submission_id', 'template_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_answers');
        Schema::dropIfExists('ipcrf_submissions');
    }
};
