<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Location;

interface OutputWriterFactory
{
    public function makeWriter(string $outputFolderPath, Location $location): OutputWriter;
}
