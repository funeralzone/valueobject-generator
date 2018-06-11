<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions;

final class PropertyDoesNotExist extends \Exception
{
    public function __construct(string $propertyName)
    {
        parent::__construct(sprintf('The "%s" property does not exist', $propertyName));
    }
}
