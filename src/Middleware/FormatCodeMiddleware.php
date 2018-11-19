<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;

final class FormatCodeMiddleware implements Middleware
{
    public function getExecutionStage(): MiddlewareExecutionStage
    {
        return MiddlewareExecutionStage::POST_GENERATION();
    }

    public function run(Definition $definition, string $outputFolderPath)
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
            '%s %s/%s %s',
            'php',
            __DIR__,
            '../../phars/php-cs-fixer-v2.phar',
            'fix'
        );
    }
}

