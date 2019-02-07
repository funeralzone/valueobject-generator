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
    private $stage;
    private $splatArguments;

    public function __construct(
        ModelDecoratorHookType $type,
        ModelDecoratorHookTargetMethod $callable,
        ModelDecoratorHookStage $stage,
        ModelDecoratorHookSplatArguments $splatArguments
    ) {
        $this->type = $type;
        $this->method = $callable;
        $this->stage = $stage;
        $this->splatArguments = $splatArguments;
    }

    public static function fromNative($native)
    {
        return new ModelDecoratorHook(
            ModelDecoratorHookType::fromNative($native['type'] ?? null),
            ModelDecoratorHookTargetMethod::fromNative($native['method'] ?? null),
            ModelDecoratorHookStage::fromNative($native['stage'] ?? null),
            ModelDecoratorHookSplatArguments::fromNative($native['splatArguments'] ?? null)
        );
    }

    public function type(): ModelDecoratorHookType
    {
        return $this->type;
    }

    public function method(): ModelDecoratorHookTargetMethod
    {
        return $this->method;
    }

    public function getStage(): ModelDecoratorHookStage
    {
        return $this->stage;
    }

    public function getSplatArguments(): ModelDecoratorHookSplatArguments
    {
        return $this->splatArguments;
    }
}
