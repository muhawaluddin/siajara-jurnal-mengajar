<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_student_id_date_unique');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['student_id', 'date', 'teaching_journal_id'], 'attendances_student_date_journal_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_student_date_journal_unique');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['student_id', 'date']);
        });
    }
};
