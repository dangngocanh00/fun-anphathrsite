<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ref_code', 20)->nullable()->unique()->after('email');
            $table->boolean('is_active')->default(true)->after('password');
            $table->softDeletes();
        });

        Schema::table('pipeline_logs', function (Blueprint $table) {
            $table->string('ref_code', 20)->nullable()->after('moved_by');
            $table->index('ref_code');
        });
    }

    public function down(): void
    {
        Schema::table('pipeline_logs', function (Blueprint $table) {
            $table->dropIndex(['ref_code']);
            $table->dropColumn('ref_code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['is_active']);
            $table->dropUnique(['ref_code']);
            $table->dropColumn('ref_code');
        });
    }
};
