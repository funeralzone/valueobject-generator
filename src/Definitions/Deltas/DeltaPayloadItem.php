<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Deltas;

final class DeltaPayloadItem
{
    private $delta;
    private $propertyName;
    private $useRootData;

    public function __construct(
        Delta $delta,
        string $propertyName,
        bool $useRootData
    ) {
        $this->delta = $delta;
        $this->propertyName = $propertyName;
        $this->useRootData = $useRootData;
    }

    public function delta(): Delta
    {
        return $this->delta;
    }

    public function propertyName(): string
    {
        return $this->propertyName;
    }

    public function useRootData(): bool
    {
        return $this->useRootData;
    }
}
