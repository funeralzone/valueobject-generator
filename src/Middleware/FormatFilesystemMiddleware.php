<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

final class FormatFilesystemMiddleware implements Middleware
{
    public function getExecutionStage(): MiddlewareExecutionStage
    {
        return MiddlewareExecutionStage::PRE_GENERATION();
    }

    public function run(Definition $definition, string $outputFolderPath, ?Model $model): void
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
