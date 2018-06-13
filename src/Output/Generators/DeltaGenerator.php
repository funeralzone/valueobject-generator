<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Definitions\Deltas\Delta;

interface DeltaGenerator
{
    public function generate(Delta $delta): void;
}
