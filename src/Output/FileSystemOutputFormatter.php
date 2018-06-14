<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

final class FileSystemOutputFormatter implements OutputFormatter
{
    public function format(string $outputFolderPath): void
    {
        $this->cleanFolder($outputFolderPath);
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
