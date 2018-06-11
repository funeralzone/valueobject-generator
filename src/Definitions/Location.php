<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

final class Location
{
    private $rootNamespace;
    private $relativeNamespace;
    private $name;

    public function __construct(
        array $rootNamespace,
        array $relativeNamespace,
        string $name
    ) {
        $this->rootNamespace = $rootNamespace;
        $this->relativeNamespace = $relativeNamespace;
        $this->name = $name;
    }

    public function rootNamespace(): array
    {
        return $this->rootNamespace;
    }

    public function relativeNamespace(): array
    {
        return $this->relativeNamespace;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function namespace(): array
    {
        return array_merge($this->rootNamespace, $this->relativeNamespace);
    }

    public function namespaceAsString(): string
    {
        return implode('\\', $this->namespace());
    }

    public function path(): string
    {
        return '\\' . ltrim($this->namespaceAsString() . '\\' . $this->name(), '\\');
    }

    public function isSame(Location $location): bool
    {
        return $this->path() === $location->path();
    }
}
