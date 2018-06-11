<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions\PropertyDoesNotExist;

class ModelProperties
{
    private $properties;

    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    public function has(string $item): bool
    {
        return array_key_exists($item, $this->properties);
    }

    public function get(string $item)
    {
        if ($this->has($item)) {
            return $this->properties[$item];
        } else {
            throw new PropertyDoesNotExist($item);
        }
    }
}
