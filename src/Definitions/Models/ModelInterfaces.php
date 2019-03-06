<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions\InvalidModelInterface;

class ModelInterfaces
{
    private $interfaces;

    public function __construct(array $interfaces)
    {
        foreach ($interfaces as $interface) {
            if (! $interface instanceof ModelInterface) {
                throw new InvalidModelInterface;
            }
        }

        $this->interfaces = $interfaces;
    }

    public function all(): array
    {
        return $this->interfaces;
    }
}
