<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Deltas;

final class DeltaPayloadItem
{
    private $delta;
    private $propertyName;

    public function __construct(
        Delta $delta,
        string $propertyName
    ) {
        $this->delta = $delta;
        $this->propertyName = $propertyName;
    }

    public function delta(): Delta
    {
        return $this->delta;
    }

    public function propertyName(): string
    {
        return $this->propertyName;
    }
}
