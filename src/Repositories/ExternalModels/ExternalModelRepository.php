<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ExternalModels;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

interface ExternalModelRepository
{
    public function get(string $item): Model;
    public function has(string $item): bool;
}
