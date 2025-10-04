<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'uuid'      => (string) Str::uuid(),
            'name'      => 'Device ' . $this->faker->unique()->numerify('###'),
            'serial'    => strtoupper(Str::random(10)),
            'status'    => 'active',
            'api_token' => bin2hex(random_bytes(32)),
        ];
    }
}
