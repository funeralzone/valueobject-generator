<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators;

use Funeralzone\ValueObjects\Sets\NonNullSet;

final class ModelDecoratorHookSet extends NonNullSet
{
    public static function valuesShouldBeUnique(): bool
    {
        return true;
    }

    public function typeToEnforce(): string
    {
        return ModelDecoratorHook::class;
    }

    public function allByType(string $type)
    {
        $matches = [];
        foreach ($this->toArray() as $item) {
            /** @var ModelDecoratorHook $item */
            if ($item->type()->toNative() === $type) {
                $matches[] = $item;
            }
        }
        return $matches;
    }

    public function all(): array
    {
        return $this->toArray();
    }
}
