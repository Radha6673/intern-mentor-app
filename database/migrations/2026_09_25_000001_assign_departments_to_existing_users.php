<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $departments = [
            'web_developer',
            'android_developer',
            'ios_developer',
            'devops',
            'ai_developer',
            'business_analyst',
            'data_analyst',
        ];

        // Ensure default mentor has web_developer
        DB::table('users')->where('email', 'mentor@example.com')->update(['department' => 'web_developer']);

        // Ensure default intern has web_developer
        DB::table('users')->where('email', 'intern@example.com')->update(['department' => 'web_developer']);

        // Assign departments to all mentors who have null department
        $mentors = DB::table('users')->where('role', 'mentor')->whereNull('department')->get();
        foreach ($mentors as $index => $mentor) {
            $dept = $departments[$index % count($departments)];
            DB::table('users')->where('id', $mentor->id)->update(['department' => $dept]);
        }

        // Assign departments evenly across all 7 departments to all interns who have null department
        $interns = DB::table('users')->where('role', 'intern')->whereNull('department')->get();
        foreach ($interns as $index => $intern) {
            $dept = $departments[$index % count($departments)];
            DB::table('users')->where('id', $intern->id)->update(['department' => $dept]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
