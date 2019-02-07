<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\GeneratedClassPaths;

use Funeralzone\ValueObjects\CompositeTrait;
use Funeralzone\ValueObjects\ValueObject;

final class GeneratedClassPath implements ValueObject
{
    use CompositeTrait;

    private $type;
    private $fullyQualifiedPath;

    public function __construct(
        GeneratedClassType $type,
        GeneratedFullyQualifiedPath $fullyQualifiedPath
    ) {
        $this->type = $type;
        $this->fullyQualifiedPath = $fullyQualifiedPath;
    }

    public function getType(): GeneratedClassType
    {
        return $this->type;
    }

    public function getFullyQualifiedPath(): GeneratedFullyQualifiedPath
    {
        return $this->fullyQualifiedPath;
    }

    public static function fromNative($native)
    {
        return new static(
            new GeneratedClassType($native['type']),
            new GeneratedFullyQualifiedPath($native['path'])
        );
    }

    public function getClassName(): string
    {
        return trim(strrchr($this->fullyQualifiedPath->toNative(), '\\'), '\\');
    }

    public function __toString(): string
    {
        return $this->fullyQualifiedPath->toNative();
    }
}
