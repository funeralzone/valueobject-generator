<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\Templates;

use Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions\TemplateDoesNotExist;

final class NullTemplateRepository implements TemplateRepository
{
    public function has(string $item): bool
    {
        return false;
    }

    public function get(string $item): Template
    {
        throw new TemplateDoesNotExist($item);
    }

    public function all(): array
    {
        return [];
    }
}
