<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\ProgressReporting;

interface ProgressReporter
{
    public function generateModelsProgress(int $totalCount, int $generatedCount): void;
}
