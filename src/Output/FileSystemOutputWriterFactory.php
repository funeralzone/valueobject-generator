<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Output\Exceptions\OutputLocationCouldNotBeCreated;

final class FileSystemOutputWriterFactory implements OutputWriterFactory
{
    private $modelRootFolderPathFactory;

    public function __construct(ModelRootFolderPathFactory $modelRootFolderPathFactory)
    {
        $this->modelRootFolderPathFactory = $modelRootFolderPathFactory;
    }

    public function makeWriter(string $outputFolderPath, Model $model): OutputWriter
    {
        $targetFolderPath = $this->modelRootFolderPathFactory->makeRootFolderPath($outputFolderPath, $model);
        $this->makeDirectory($targetFolderPath);
        return new FileSystemOutputWriter($targetFolderPath);
    }

    private function makeDirectory(string $path)
    {
        if (!is_dir($path)) {
            if (! mkdir($path, 0755, true)) {
                throw new OutputLocationCouldNotBeCreated($path);
            }
        }
    }
}
