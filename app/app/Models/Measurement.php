<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Measurement extends Model
{
    use HasFactory;
    use OwnedByCurrentOwner;

    public const UPDATED_AT = null;
    public $timestamps = false;

    protected $table = 'measurements';

    protected $fillable = [
        'device_id',
        'recorded_at',
        'temperature_c',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'recorded_at'   => 'datetime',
        'temperature_c' => 'decimal:2',
        'payload'       => 'array',
        'created_at'    => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}
