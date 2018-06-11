<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions;

final class InvalidModel extends \Exception
{
    public function __construct()
    {
        parent::__construct(sprintf('The supplied model is not valid'));
    }
}
