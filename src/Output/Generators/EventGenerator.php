<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Definitions\Events\Event;

interface EventGenerator
{
    public function generate(Event $model, string $outputFolderPath);
}
