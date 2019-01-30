<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware\Exceptions;

use Exception;

final class InvalidMiddlewareRunProfileItemWasSupplied extends Exception
{
    public function __construct()
    {
        parent::__construct('An invalid middleware run profile item was supplied');
    }
}
