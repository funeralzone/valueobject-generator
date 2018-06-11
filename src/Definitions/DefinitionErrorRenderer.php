<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

interface DefinitionErrorRenderer
{
    public function render(array $errors): void;
}
