<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\Templates;

use Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions\InvalidTemplateSupplied;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions\TemplateDoesNotExist;

final class ArrayTemplateRepository implements TemplateRepository
{
    private $types = [];

    public function __construct(array $types)
    {
        foreach ($types as $type) {
            if ($type instanceof Template) {
                $this->types[$type->type()] = $type;
            } else {
                throw new InvalidTemplateSupplied;
            }
        }
    }

    public function has(string $item): bool
    {
        return array_key_exists($item, $this->types);
    }

    public function get(string $item): Template
    {
        if ($this->has($item)) {
            return $this->types[$item];
        } else {
            throw new TemplateDoesNotExist($item);
        }
    }
}
