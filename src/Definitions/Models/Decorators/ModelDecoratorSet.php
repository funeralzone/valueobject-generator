<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators;

use Funeralzone\ValueObjects\Sets\NonNullSet;

final class ModelDecoratorSet extends NonNullSet
{
    public static function valuesShouldBeUnique(): bool
    {
        return true;
    }

    public function typeToEnforce(): string
    {
        return ModelDecorator::class;
    }

    public function all(): array
    {
        return $this->toArray();
    }
}
