<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Alert;

interface AlertRepositoryInterface
{
    public function create(array $data): Alert;
}
