<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('subject_one');
            $table->enum('grade_one', ['A', 'B', 'C', 'D', 'E', 'F', 'X', 'Q', 'W']);
            $table->unsignedTinyInteger('point_one')->default(0);
            $table->string('subject_two');
            $table->enum('grade_two', ['A', 'B', 'C', 'D', 'E', 'F', 'X', 'Q', 'W']);
            $table->unsignedTinyInteger('point_two')->default(0);
            $table->string('subject_three');
            $table->enum('grade_three', ['A', 'B', 'C', 'D', 'E', 'F', 'X', 'Q', 'W']);
            $table->unsignedTinyInteger('point_three')->default(0);
            $table->unsignedTinyInteger('bonus_point')->default(0);
            $table->unsignedTinyInteger('total_point')->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
