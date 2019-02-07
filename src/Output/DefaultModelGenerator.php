<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

class DefaultModelGenerator implements ModelGenerator
{
    private $writerFactory;

    public function __construct(
        OutputWriterFactory $writerFactory
    ) {
        $this->writerFactory = $writerFactory;
    }

    public function generate(Model $model, string $outputFolderPath)
    {
        if ($model->externalToDefinition() === false) {
            $outputWriter = $this->writerFactory->makeWriter($outputFolderPath, $model);

            $model->type()->generate(
                $outputWriter,
                $model
            );

            foreach ($model->children()->all() as $childModel) {
                $this->generate($childModel, $outputFolderPath);
            }
        }
    }
}
