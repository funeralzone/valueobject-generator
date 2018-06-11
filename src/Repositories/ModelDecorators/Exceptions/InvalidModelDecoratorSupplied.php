<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\Exceptions;

final class InvalidModelDecoratorSupplied extends \Exception
{
    public function __construct()
    {
        parent::__construct('An invalid model type decorator has been supplied');
    }
}
