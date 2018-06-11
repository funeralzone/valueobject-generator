<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

final class FileSystemOutputFormatter implements OutputFormatter
{
    private $rootFolderPath;

    public function __construct(string $rootFolderPath)
    {
        $this->rootFolderPath = rtrim($rootFolderPath, '/') . '/';
    }

    public function format(): void
    {
        $this->cleanFolder($this->rootFolderPath);
    }

    private function cleanFolder(string $folderPath): void
    {
        if (is_dir($folderPath)) {
            $handle = opendir($folderPath);

            while ($item = readdir($handle)) {
                if ($item != '.' && $item != '..') {
                    $itemPath = $folderPath . $item;
                    if (is_file($itemPath)) {
                        unlink($itemPath);
                    } else {
                        $this->cleanFolder($itemPath . '/');
                        rmdir($itemPath);
                    }
                }
            }

            closedir($handle);
        }
    }
}
