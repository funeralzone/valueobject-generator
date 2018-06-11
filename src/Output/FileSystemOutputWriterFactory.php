<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Output\Exceptions\OutputLocationCouldNotBeCreated;

final class FileSystemOutputWriterFactory implements OutputWriterFactory
{
    private $rootFolderPath;

    public function __construct(string $rootFolderPath)
    {
        $this->rootFolderPath = rtrim($rootFolderPath, '/') . '/';
    }

    public function makeWriter(Location $location): OutputWriter
    {
        $targetFolderPath = $this->rootFolderPath . implode('/', $location->relativeNamespace()) . '/';
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
