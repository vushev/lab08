<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OwnedByCurrentOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;
    use OwnedByCurrentOwner;

    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'user_id',
        'measurement_id',
        'type',
        'message',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
}
