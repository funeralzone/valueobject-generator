<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Twig\Filters\Exceptions;

use Exception;

final class InvalidTwigFilterWasSupplied extends Exception
{
    public function __construct()
    {
        parent::__construct('An invalid Twig filter was supplied');
    }
}
