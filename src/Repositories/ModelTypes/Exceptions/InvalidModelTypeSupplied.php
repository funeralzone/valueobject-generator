<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\Exceptions;

final class InvalidModelTypeSupplied extends \Exception
{
    public function __construct()
    {
        parent::__construct('An invalid type has been supplied');
    }
}
