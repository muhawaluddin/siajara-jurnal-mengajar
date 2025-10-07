<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('classroom_id')->nullable()->after('name')->constrained()->nullOnDelete();
        });

        $classes = DB::table('students')->select('class')->distinct()->pluck('class')->filter();

        $classroomMap = [];

        foreach ($classes as $className) {
            $classroomId = DB::table('classrooms')->insertGetId([
                'name' => $className,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $classroomMap[$className] = $classroomId;
        }

        foreach ($classroomMap as $className => $classroomId) {
            DB::table('students')->where('class', $className)->update([
                'classroom_id' => $classroomId,
            ]);
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('class');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('class')->nullable()->after('name');
        });

        $studentClassrooms = DB::table('students')
            ->whereNotNull('classroom_id')
            ->get(['id', 'classroom_id']);

        $classroomNames = DB::table('classrooms')->pluck('name', 'id');

        foreach ($studentClassrooms as $student) {
            DB::table('students')->where('id', $student->id)->update([
                'class' => $classroomNames[$student->classroom_id] ?? null,
            ]);
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropColumn('classroom_id');
        });

        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classrooms');
    }
};
