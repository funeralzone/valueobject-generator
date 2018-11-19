<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelNamespace;
use Funeralzone\ValueObjectGenerator\Output\Exceptions\OutputLocationCouldNotBeCreated;

final class FileSystemOutputWriterFactory implements OutputWriterFactory
{
    public function makeWriter(string $outputFolderPath, ModelNamespace $namespace): OutputWriter
    {
        $targetFolderPath = $outputFolderPath. implode('/', $namespace->relativeNamespace()) . '/';
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
