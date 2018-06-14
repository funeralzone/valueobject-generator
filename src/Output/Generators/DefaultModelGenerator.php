<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Output\OutputTemplateRenderer;
use Funeralzone\ValueObjectGenerator\Output\OutputWriterFactory;

class DefaultModelGenerator implements ModelGenerator
{
    private $writerFactory;
    private $outputTemplateRenderer;

    public function __construct(
        OutputWriterFactory $writerFactory,
        OutputTemplateRenderer $outputTemplateRenderer
    ) {
        $this->writerFactory = $writerFactory;
        $this->outputTemplateRenderer = $outputTemplateRenderer;
    }

    public function generate(Model $model, string $outputFolderPath)
    {
        if ($model->creatable()) {
            $outputWriter = $this->writerFactory->makeWriter($outputFolderPath, $model->referenceLocation());

            $model->type()->generate(
                $this->outputTemplateRenderer,
                $outputWriter,
                $model
            );

            foreach ($model->children()->all() as $childModel) {
                $this->generate($childModel, $outputFolderPath);
            }
        }
    }
}
