<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions\PropertyDoesNotExist;
use \Exception;
use \ArrayAccess;

class ModelProperties implements ArrayAccess
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

    public function all(): array
    {
        return $this->properties;
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception('This object is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new Exception('This object is immutable');
    }
}
