<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Alert;

final class AlertRepository
{
    public function create(array $data): Alert
    {
        return Alert::create($data);
    }
}
