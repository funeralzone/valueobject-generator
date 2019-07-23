<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Exceptions;

use Exception;

final class InvalidNativeDefinitionSupplied extends Exception
{
    public function __construct()
    {
        parent::__construct('a supplied native definition is not valid');
    }
}
