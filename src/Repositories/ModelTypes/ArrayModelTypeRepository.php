<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes;

use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\Exceptions\InvalidModelTypeSupplied;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\Exceptions\ModelTypeDoesNotExist;

final class ArrayModelTypeRepository implements ModelTypeRepository
{
    private $types = [];

    public function __construct(array $types)
    {
        foreach ($types as $type) {
            if ($type instanceof ModelType) {
                $this->types[$type->type()] = $type;
            } else {
                throw new InvalidModelTypeSupplied;
            }
        }
    }

    public function has(string $item): bool
    {
        return array_key_exists($item, $this->types);
    }

    public function get(string $item): ModelType
    {
        if ($this->has($item)) {
            return $this->types[$item];
        } else {
            throw new ModelTypeDoesNotExist($item);
        }
    }


    public function all(): array
    {
        return $this->types;
    }
}
