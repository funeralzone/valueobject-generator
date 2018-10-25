<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators;

use Funeralzone\ValueObjects\CompositeTrait;
use Funeralzone\ValueObjects\ValueObject;

class ModelDecoratorHook implements ValueObject
{
    use CompositeTrait;

    private $type;
    private $method;

    public function __construct(
        ModelDecoratorHookType $type,
        ModelDecoratorHookTargetMethod $callable
    ) {
        $this->type = $type;
        $this->method = $callable;
    }

    public static function fromNative($native)
    {
        return new ModelDecoratorHook(
            new ModelDecoratorHookType($native['type'] ?? null),
            new ModelDecoratorHookTargetMethod($native['method'] ?? null)
        );
    }

    /**
     * @return ModelDecoratorHookType
     */
    public function type(): ModelDecoratorHookType
    {
        return $this->type;
    }

    /**
     * @return ModelDecoratorHookTargetMethod
     */
    public function method(): ModelDecoratorHookTargetMethod
    {
        return $this->method;
    }
}
