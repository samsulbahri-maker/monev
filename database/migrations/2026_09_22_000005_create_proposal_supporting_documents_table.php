<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_supporting_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->string('document_name', 120);
            $table->decimal('amount', 18, 2)->default(0);
            $table->timestamps();
            $table->index(['proposal_id', 'document_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_supporting_documents');
    }
};
