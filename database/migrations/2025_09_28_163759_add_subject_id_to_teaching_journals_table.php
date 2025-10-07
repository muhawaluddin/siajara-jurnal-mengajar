<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teaching_journals', function (Blueprint $table) {
            $table->foreignId('subject_id')
                ->nullable()
                ->after('guru_id')
                ->constrained()
                ->nullOnDelete();
        });

        $journals = DB::table('teaching_journals')->select('id', 'mata_pelajaran')->get();

        foreach ($journals as $journal) {
            $subjectId = DB::table('subjects')->where('name', $journal->mata_pelajaran)->value('id');

            if ($subjectId) {
                DB::table('teaching_journals')->where('id', $journal->id)->update(['subject_id' => $subjectId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teaching_journals', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }
};
