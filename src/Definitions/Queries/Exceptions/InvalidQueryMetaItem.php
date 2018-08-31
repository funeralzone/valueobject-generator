<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Queries\Exceptions;

final class InvalidQueryMetaItem extends \Exception
{
    public function __construct()
    {
        parent::__construct(sprintf('The supplied query meta item is not valid'));
    }
}
