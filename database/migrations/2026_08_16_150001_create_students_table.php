<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('surname');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('foundation_number')->unique();
            $table->string('examination_number')->unique();
            $table->foreignId('subject_one_id')->nullable()->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('subject_two_id')->nullable()->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('subject_three_id')->nullable()->constrained('subjects')->cascadeOnDelete();
            $table->string('passport')->nullable();
            $table->string('session')->default('2025/2026');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['surname', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
