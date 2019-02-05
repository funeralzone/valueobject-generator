<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

final class FormatCodeMiddleware implements Middleware
{
    public function getExecutionStage(): MiddlewareExecutionStage
    {
        return MiddlewareExecutionStage::POST_GENERATION();
    }

    public function run(Definition $definition, string $outputFolderPath, ?Model $model): void
    {
        $command = sprintf(
            '%s %s',
            $this->getCodeFormatCommand(),
            $outputFolderPath
        );

        exec($command);
    }

    private function getCodeFormatCommand(): string
    {
        return sprintf(
            'php %s/../../phars/php-cs-fixer-v2.phar fix --using-cache=no',
            __DIR__
        );
    }
}
