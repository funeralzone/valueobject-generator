<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators;

interface ModelDecoratorRepository
{
    public function get(string $item): ModelDecorator;
    public function has(string $item): bool;
    public function all(): array;
}
