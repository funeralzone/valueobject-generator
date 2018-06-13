<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Deltas\Exceptions;

final class InvalidDeltaPayloadItem extends \Exception
{
    public function __construct()
    {
        parent::__construct(sprintf('The supplied delta payload item is not valid'));
    }
}
