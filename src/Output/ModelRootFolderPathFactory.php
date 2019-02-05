<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

interface ModelRootFolderPathFactory
{
    public function makeRootFolderPath(string $outputFolderPath, Model $model): string;
}
