<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes;

interface ModelTypeRepository
{
    public function get(string $item): ModelType;
    public function has(string $item): bool;
    public function all(): array;
}
