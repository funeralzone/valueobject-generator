<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Queries\Exceptions;

final class InvalidQuery extends \Exception
{
    public function __construct()
    {
        parent::__construct(sprintf('The supplied query is not valid'));
    }
}
