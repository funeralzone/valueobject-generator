<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Exceptions;

final class InvalidDefinition extends \Exception
{
    public function __construct(array $errors = [])
    {
        parent::__construct('The supplied definition is not valid');
    }
}
