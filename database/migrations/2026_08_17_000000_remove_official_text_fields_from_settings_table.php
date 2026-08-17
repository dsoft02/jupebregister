<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->whereIn('key', [
            'director_credentials',
            'vice_chancellor_name',
            'vice_chancellor_credentials',
        ])->delete();
    }

    public function down(): void
    {
        DB::table('settings')->insert([
            ['key' => 'director_credentials', 'value' => 'Director, PAAU Foundation School', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'vice_chancellor_name', 'value' => 'Prof. Suleiman O. Abdul', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'vice_chancellor_credentials', 'value' => 'Vice Chancellor, Prince Abubakar Audu University, Anyigba', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
