<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Exceptions;

use Exception;

final class OutputLocationDoesNotExist extends Exception
{
    public function __construct(string $location)
    {
        parent::__construct(sprintf('The output location "%s" does not exist', $location));
    }
}
