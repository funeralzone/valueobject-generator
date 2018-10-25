<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators;

use Funeralzone\ValueObjects\Scalars\StringTrait;
use Funeralzone\ValueObjects\ValueObject;

final class ModelDecoratorHookTargetMethod implements ValueObject
{
    use StringTrait;

    public function __toString()
    {
        return $this->toNative();
    }
}
