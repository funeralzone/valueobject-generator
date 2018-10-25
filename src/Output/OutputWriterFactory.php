<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelNamespace;

interface OutputWriterFactory
{
    public function makeWriter(string $outputFolderPath, ModelNamespace $namespace): OutputWriter;
}
