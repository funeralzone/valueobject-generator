<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Definitions\Commands\Command;

interface CommandGenerator
{
    public function generate(Command $command, string $outputFolderPath): void;
}
