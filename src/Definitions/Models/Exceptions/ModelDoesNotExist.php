<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions;

final class ModelDoesNotExist extends \Exception
{
    public function __construct(array $path)
    {
        parent::__construct(sprintf('The supplied model "%s" does not exist', implode('\\', $path)));
    }
}
