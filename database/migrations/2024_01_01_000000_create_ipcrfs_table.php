<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipcrfs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('province');
            $table->string('municipality');
            $table->string('evaluated_file_path');
            $table->string('scanned_file_path');
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipcrfs');
    }
};