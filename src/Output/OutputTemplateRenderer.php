<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

interface OutputTemplateRenderer
{
    public function render(string $templateName, array $templateVariables);
}
