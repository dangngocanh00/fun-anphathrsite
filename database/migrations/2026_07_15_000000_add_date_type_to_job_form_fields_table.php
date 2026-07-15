<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_form_fields', function (Blueprint $table) {
            $table->enum('type', ['text', 'select', 'radio', 'textarea', 'date'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('job_form_fields', function (Blueprint $table) {
            $table->enum('type', ['text', 'select', 'radio', 'textarea'])->change();
        });
    }
};
