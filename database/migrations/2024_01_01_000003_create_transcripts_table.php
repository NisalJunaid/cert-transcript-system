<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->boolean('pass_with_distinction')->default(false);
            $table->boolean('deans_award')->default(false);
            $table->date('completed_date')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
