<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\ProgressReporting;

class StdOutProgressReporter implements ProgressReporter
{
    public function generateModelsProgress(int $totalCount, int $generatedCount): void
    {
        if ($generatedCount > 1) {
            print "\033" . "[1A";
        }

        print sprintf(
            "Models generated: %d/%d (%d%%)\n",
            $generatedCount,
            $totalCount,
            ceil(abs(($generatedCount / $totalCount) * 100))
        );
    }
}
