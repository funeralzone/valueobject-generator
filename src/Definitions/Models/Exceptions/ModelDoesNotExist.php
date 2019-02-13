<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions;

final class ModelDoesNotExist extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('The model "%s" does not exist', $name));
    }
}
