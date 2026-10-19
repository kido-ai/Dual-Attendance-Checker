<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Creates the system_settings table
|--------------------------------------------------------------------------
| A simple key/value store is the right structure here rather than a
| single-row table with named columns. Reasons:
|   1. Adding a new setting never requires a schema migration -- just
|      insert a new key. Settings::get('new_key') works immediately.
|   2. The Settings model's get()/set() API is clean and consistent
|      regardless of how many keys exist.
|
| Default values are seeded in the migration itself so the settings page
| has real data the moment the admin first visits it, even on a fresh
| install with no prior configuration.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed defaults so the Settings page is never empty
        $defaults = [
            ['key' => 'system_name',          'value' => 'Dual Auth Attendance'],
            ['key' => 'school_name',           'value' => 'Your School Name'],
            ['key' => 'school_logo',           'value' => null],
            ['key' => 'qr_expiration_days',    'value' => '365'],
            ['key' => 'face_match_threshold',  'value' => '0.55'],
        ];

        foreach ($defaults as $row) {
            DB::table('system_settings')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
