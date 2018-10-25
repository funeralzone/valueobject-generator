<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Testing;

class ModelTestStipulations
{
    private $fromNativeValueCode;
    private $useStatements;

    public function __construct(
        string $fromNativeValueCode,
        array $useStatements = []
    ) {
        $this->fromNativeValueCode = $fromNativeValueCode;
        $this->useStatements = $useStatements;
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
