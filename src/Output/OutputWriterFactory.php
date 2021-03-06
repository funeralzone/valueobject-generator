<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

interface OutputWriterFactory
{
    public function makeWriter(string $outputFolderPath, Model $model): OutputWriter;
}
