<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

final class ModelNamespace
{
    private $rootNamespace;
    private $relativeNamespace;

    public function __construct(
        array $rootNamespace,
        array $relativeNamespace
    ) {
        $this->rootNamespace = $rootNamespace;
        $this->relativeNamespace = $relativeNamespace;
    }

    public function rootNamespace(): array
    {
        return $this->rootNamespace;
    }

    public function relativeNamespace(): array
    {
        return $this->relativeNamespace;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return implode(
            '\\',
            array_merge($this->rootNamespace, $this->relativeNamespace)
        );
    }

    public function isSame(ModelNamespace $location): bool
    {
        return $this->toString() === $location->toString();
    }
}
