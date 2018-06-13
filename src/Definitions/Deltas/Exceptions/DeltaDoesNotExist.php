<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Deltas\Exceptions;

final class DeltaDoesNotExist extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('The supplied delta "%s" does not exist', $name));
    }
}
