<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Testing;

class ModelTestStipulations
{
    private $constructorValueCode;
    private $fromNativeValueCode;
    private $useStatements;

    public function __construct(
        string $constructorValueCode,
        string $fromNativeValueCode,
        array $useStatements = []
    ) {
        $this->constructorValueCode = $constructorValueCode;
        $this->fromNativeValueCode = $fromNativeValueCode;
        $this->useStatements = $useStatements;
    }

    public function constructorValueCode(): string
    {
        return $this->constructorValueCode;
    }

    public function fromNativeValueCode(): string
    {
        return $this->fromNativeValueCode;
    }

    public function useStatements(): array
    {
        return $this->useStatements;
    }
}
