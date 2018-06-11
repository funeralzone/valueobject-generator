<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Output\Exceptions\OutputLocationDoesNotExist;

final class FileSystemOutputWriter implements OutputWriter
{
    private $rootFolderPath;

    public function __construct(string $rootFolderPath)
    {
        if (is_dir($rootFolderPath)) {
            $this->rootFolderPath = $rootFolderPath;
        } else {
            throw new OutputLocationDoesNotExist($rootFolderPath);
        }
    }

    public function write(string $name, string $content)
    {
        $targetFilePath = $this->rootFolderPath.$name;
        file_put_contents($targetFilePath, $content);
    }
}
