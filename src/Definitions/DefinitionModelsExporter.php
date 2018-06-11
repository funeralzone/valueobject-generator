<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Funeralzone\ValueObjectGenerator\Definitions\Models\DefinedModel;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\ArrayExternalModelRepository;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\ExternalModelRepository;

final class DefinitionModelsExporter
{
    public function export(Definition $definition): ExternalModelRepository
    {
        $externalModels = [];
        foreach ($definition->models()->all() as $model) {
            $externalModels = array_merge($externalModels, $this->extractFromModel($model));
        }
        return new ArrayExternalModelRepository($externalModels);
    }

    private function extractFromModel(DefinedModel $model): array
    {
        $externalModels = [];
        if ($model->export()) {
            $externalModels[] = $model;

            foreach ($model->children()->all() as $childModel) {
                /** @var DefinedModel $childModel */
                $externalModels = array_merge($externalModels, $this->extractFromModel($childModel));
            }
        }
        return $externalModels;
    }
}
