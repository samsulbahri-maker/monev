<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opds', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type', 30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('program_opd', function (Blueprint $table) {
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opd_id')->constrained()->cascadeOnDelete();
            $table->primary(['program_id', 'opd_id']);
        });

        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->foreignId('opd_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('budget_year');
            $table->string('work_type', 80);
            $table->text('work_description')->nullable();
            $table->text('location')->nullable();
            $table->string('map_url')->nullable();
            $table->decimal('main_budget', 18, 2)->nullable();
            $table->json('supporting_documents')->nullable();
            $table->decimal('supporting_budget', 18, 2)->nullable();
            $table->date('execution_date')->nullable();
            $table->string('status', 40)->default('Tercantum Dalam DPA');
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->text('achievement')->nullable();
            $table->string('evidence_path')->nullable();
            $table->timestamps();
            $table->index(['budget_year', 'status']);
        });

        Schema::create('progress_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->string('status', 40);
            $table->unsignedTinyInteger('percentage')->default(0);
            $table->text('notes')->nullable();
            $table->string('evidence_path')->nullable();
            $table->timestamps();
            $table->unique(['proposal_id', 'month']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('opd_id')->nullable()->after('id')->constrained('opds')->nullOnDelete();
            $table->string('role', 30)->default('viewer_opd')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['opd_id']);
            $table->dropColumn(['opd_id', 'role']);
        });
        Schema::dropIfExists('progress_updates');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('program_opd');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('opds');
    }
};
