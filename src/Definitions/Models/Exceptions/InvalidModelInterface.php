<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions;

final class InvalidModelInterface extends \Exception
{
    public function __construct()
    {
        parent::__construct(sprintf('The supplied model interface item is not valid'));
    }
}
