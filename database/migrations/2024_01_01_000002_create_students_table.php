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
            $table->string('certificate_serial_number')->nullable();
            $table->string('name');
            $table->string('national_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('student_identifier')->nullable();
            $table->string('program')->nullable();
            $table->string('level')->nullable();
            $table->timestamps();
            $table->index(['certificate_serial_number']);
            $table->index(['student_identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
