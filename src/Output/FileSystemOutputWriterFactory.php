<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Output\Exceptions\OutputLocationCouldNotBeCreated;

final class FileSystemOutputWriterFactory implements OutputWriterFactory
{
    public function makeWriter(string $outputFolderPath, Location $location): OutputWriter
    {
        $targetFolderPath = $outputFolderPath. implode('/', $location->relativeNamespace()) . '/';
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
