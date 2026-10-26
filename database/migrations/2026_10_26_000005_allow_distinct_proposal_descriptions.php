<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->index('program_id', 'proposals_program_id_index');
            $table->index('opd_id', 'proposals_opd_id_index');
            $table->dropUnique('proposals_program_opd_year_unique');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropIndex('proposals_program_id_index');
            $table->dropIndex('proposals_opd_id_index');
            $table->unique(['program_id', 'opd_id', 'budget_year'], 'proposals_program_opd_year_unique');
        });
    }
};
