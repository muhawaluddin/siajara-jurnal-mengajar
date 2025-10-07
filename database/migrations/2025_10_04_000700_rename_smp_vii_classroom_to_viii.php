<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $from = DB::table('classrooms')->where('name', 'SMP VII')->first();

        if (! $from) {
            return;
        }

        $to = DB::table('classrooms')->where('name', 'SMP VIII')->first();

        if ($to) {
            DB::table('students')->where('classroom_id', $from->id)->update([
                'classroom_id' => $to->id,
                'updated_at' => now(),
            ]);

            DB::table('classrooms')->where('id', $from->id)->delete();

            return;
        }

        DB::table('classrooms')->where('id', $from->id)->update([
            'name' => 'SMP VIII',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $target = DB::table('classrooms')->where('name', 'SMP VIII')->first();

        if (! $target) {
            return;
        }

        $existing = DB::table('classrooms')->where('name', 'SMP VII')->first();

        if ($existing) {
            DB::table('students')->where('classroom_id', $target->id)->update([
                'classroom_id' => $existing->id,
                'updated_at' => now(),
            ]);

            DB::table('classrooms')->where('id', $target->id)->delete();

            return;
        }

        DB::table('classrooms')->where('id', $target->id)->update([
            'name' => 'SMP VII',
            'updated_at' => now(),
        ]);
    }
};
