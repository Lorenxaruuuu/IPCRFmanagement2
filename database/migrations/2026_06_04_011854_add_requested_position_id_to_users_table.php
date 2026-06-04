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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('requested_position_id')
                ->nullable()
                ->after('position_id')
                ->constrained('positions')
                ->onDelete('set null');
            
            $table->dropColumn('requested_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['requested_position_id']);
            $table->dropColumn('requested_position_id');
            $table->string('requested_role')->nullable()->after('role');
        });
    }
};
