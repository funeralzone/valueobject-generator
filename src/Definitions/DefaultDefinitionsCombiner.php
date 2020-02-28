<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Exception;
use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\DefinitionIsInvalid;

class DefaultDefinitionsCombiner implements DefinitionsCombiner
{
    public function combine(
        NativeDefinitions $nativeDefinitions
    ): NativeDefinition {

        $combinedModel = [];

        /** @var NativeDefinition $nativeDefinition */
        foreach ($nativeDefinitions->all() as $nativeDefinition) {
            try {
                if (count($nativeDefinition->getModel()) === 0) {
                    continue;
                }

                $combinedModel[] = [
                    'rootNamespace' => $nativeDefinition->getRootNamespace(),
                    'namespace' => $nativeDefinition->getNamespace(),
                    'model' => $nativeDefinition->getModel(),
                ];
            } catch (Exception $exception) {
                throw new DefinitionIsInvalid($exception->getMessage());
            }
        }

        return new NativeDefinition([
            'model' => $combinedModel,
        ]);
    }
}
