<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

class DefaultModelRootFolderPathFactory implements ModelRootFolderPathFactory
{
    public function makeRootFolderPath(string $outputFolderPath, Model $model): string
    {
        return $outputFolderPath. implode('/', $model->namespace()->relativeNamespace()) . '/';
    }
}
