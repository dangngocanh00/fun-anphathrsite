<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone', 32);
            $table->string('email')->nullable();
            $table->string('cv_link', 1024);
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('assigned_hr_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('current_stage')->default(1);
            $table->unsignedTinyInteger('ai_score')->nullable();
            $table->json('ai_flags')->nullable();
            $table->json('ai_questions')->nullable();
            $table->timestamp('ai_analyzed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['job_id', 'current_stage']);
            $table->index(['assigned_hr_id', 'current_stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
