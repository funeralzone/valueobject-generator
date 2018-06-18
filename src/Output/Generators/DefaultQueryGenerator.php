<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Generators;

use Funeralzone\ValueObjectGenerator\Conventions\ModelNamer;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelPayloadItem;
use Funeralzone\ValueObjectGenerator\Definitions\Queries\Query;
use Funeralzone\ValueObjectGenerator\Output\OutputTemplateRenderer;
use Funeralzone\ValueObjectGenerator\Output\OutputWriterFactory;

class DefaultQueryGenerator implements QueryGenerator
{
    private $writerFactory;
    private $outputTemplateRenderer;
    private $templateName;

    public function __construct(
        OutputWriterFactory $writerFactory,
        OutputTemplateRenderer $outputTemplateRenderer,
        string $templateName
    ) {
        $this->writerFactory = $writerFactory;
        $this->outputTemplateRenderer = $outputTemplateRenderer;
        $this->templateName = $templateName;
    }

    public function generate(Query $query, string $outputFolderPath): void
    {
        $modelNamer = new ModelNamer;
        $useStatements = [];
        foreach ($query->payload()->all() as $payloadItem) {
            /** @var ModelPayloadItem $payloadItem */
            $model = $payloadItem->model();
            if ($payloadItem->required()) {
                $nonNullModelName = $modelNamer->makeNonNullClassName($model->definitionName());
                $useStatements[] = $model->instantiationLocation()->namespaceAsString() . '\\' . $nonNullModelName;
            } else {
                $useStatements[] = $model->instantiationLocation()->path();
            }
        }

        $source = $this->outputTemplateRenderer->render($this->templateName, [
            'query' => $query,
            'useStatements' => array_unique($useStatements),
        ]);

        $outputWriter = $this->writerFactory->makeWriter($outputFolderPath, $query->location());
        $outputWriter->write($query->definitionName() . '.php', $source);
    }
}
