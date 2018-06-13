<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Commands\Exceptions;

final class InvalidCommand extends \Exception
{
    public function __construct()
    {
        parent::__construct(sprintf('The supplied delta is not valid'));
    }
}
