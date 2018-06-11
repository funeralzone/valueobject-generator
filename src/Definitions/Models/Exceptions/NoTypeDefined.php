<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions;

final class NoTypeDefined extends \Exception
{
    public function __construct(string $modelName)
    {
        parent::__construct(sprintf('The "%s" model must define a "type"', $modelName));
    }
}
