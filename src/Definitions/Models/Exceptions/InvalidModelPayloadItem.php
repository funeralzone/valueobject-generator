<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions;

final class InvalidModelPayloadItem extends \Exception
{
    public function __construct()
    {
        parent::__construct(sprintf('The supplied model payload item is not valid'));
    }
}
