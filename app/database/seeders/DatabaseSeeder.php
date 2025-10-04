<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $aliceId = DB::table('users')->insertGetId([
            'name' => 'Spas Lutov',
            'email' => 'spas@example.com',
            'password' => Hash::make('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $bobId = DB::table('users')->insertGetId([
            'name' => 'Simeon Todorov',
            'email' => 'simeon@example.com',
            'password' => Hash::make('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $d1Id = DB::table('devices')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'R2D2',
            'serial' => strtoupper(Str::random(10)),
            'status' => 'active',
            'api_token' => Str::random(40),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $d2Id = DB::table('devices')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'C3PO',
            'serial' => strtoupper(Str::random(10)),
            'status' => 'active',
            'api_token' => Str::random(40),
            'created_at' => $now,
            'updated_at' => $now,
        ]);


        DB::table('device_user')->insert([
            [
                'device_id'   => $d1Id,
                'user_id'     => $aliceId,
            ],
            [
                'device_id'   => $d2Id,
                'user_id'     => $bobId,
            ],
        ]);

        //normal temperature
        $m1Id = DB::table('measurements')->insertGetId([
            'device_id'     => $d1Id,
            'recorded_at'   => $now->copy()->subMinutes(30),
            'temperature_c' => 25.50,
            'payload'       => json_encode(['note' => 'normal']),
            'created_at'    => $now->copy()->subMinutes(29),
        ]);

        //high temperature
        $m2Id = DB::table('measurements')->insertGetId([
            'device_id'     => $d2Id,
            'recorded_at'   => $now->copy()->subMinutes(20),
            'temperature_c' => 31.00,
            'payload'       => json_encode(['note' => 'high']),
            'created_at'    => $now->copy()->subMinutes(19),
        ]);

        // Demo alert (high temperature)
        DB::table('alerts')->insert([
            'device_id'      => $d2Id,
            'user_id'        => $bobId,
            'measurement_id' => $m2Id,
            'type'           => 'temperature_threshold',
            'message'        => 'Temperature 31.00°C out of threshold [0.00..30.00]',
            'status'         => 'open',
            'created_at'     => $now->copy()->subMinutes(18),
        ]);
    }
}
