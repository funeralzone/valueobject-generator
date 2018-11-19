<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\GeneratedClassPaths\Exceptions;

use Exception;

final class InvalidGeneratedPathWasSupplied extends Exception
{
    public function __construct()
    {
        parent::__construct('An invalid generated model path was supplied');
    }
}
