<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

interface ModelGenerator
{
    public function generate(Model $model, string $outputFolderPath);
}
