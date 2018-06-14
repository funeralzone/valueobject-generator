<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Definitions\Queries\Query;

interface QueryGenerator
{
    public function generate(Query $query, string $outputFolderPath): void;
}
