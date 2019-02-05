<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

final class MiddlewareExecutionStage
{
    public const PRE_GENERATION = 1;
    public const POST_GENERATION = 2;
    public const POST_MODEL_INSTANCE_GENERATION = 3;

    private $value;

    private function __construct(int $stage)
    {
        $this->value = $stage;
    }

    public static function PRE_GENERATION(): self
    {
        return new static(self::PRE_GENERATION);
    }

    public static function POST_GENERATION(): self
    {
        return new static(self::POST_GENERATION);
    }

    public static function POST_MODEL_INSTANCE_GENERATION(): self
    {
        return new static(self::POST_MODEL_INSTANCE_GENERATION);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
