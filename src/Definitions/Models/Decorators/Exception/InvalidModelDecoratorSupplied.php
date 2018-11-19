<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\Exceptions;

final class InvalidModelDecoratorSupplied extends \Exception
{
    public function __construct()
    {
        parent::__construct('The supplied model decorator is not valid');
    }
}
