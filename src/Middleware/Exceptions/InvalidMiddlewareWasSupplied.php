<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Middleware\Exceptions;

use Exception;

final class InvalidMiddlewareWasSupplied extends Exception
{
    public function __construct()
    {
        parent::__construct('An invalid middleware was supplied');
    }
}
