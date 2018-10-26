<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators;

use Funeralzone\ValueObjects\CompositeTrait;
use Funeralzone\ValueObjects\ValueObject;

class ModelDecorator implements ValueObject
{
    use CompositeTrait;

    private $path;
    private $hooks;

    public function __construct(
        ModelDecoratorPath $path,
        ModelDecoratorHookSet $hooks
    ) {
        $this->path = $path;
        $this->hooks = $hooks;
    }

    public function path(): ModelDecoratorPath
    {
        return $this->path;
    }

    public function hooks(): ModelDecoratorHookSet
    {
        return $this->hooks;
    }

    public function className(): string
    {
        return trim(strrchr($this->path->toNative(), '\\'), '\\');
    }

    public static function fromNative($native)
    {
        return new static(
            new ModelDecoratorPath($native['path'] ?? null),
            new ModelDecoratorHookSet($native['hooks'] ?? null)
        );
    }
}
