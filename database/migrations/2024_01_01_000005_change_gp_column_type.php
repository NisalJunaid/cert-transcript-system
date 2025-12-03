<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE module_results MODIFY gp TINYINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE module_results MODIFY gp DECIMAL(4,2) NULL');
    }
};
