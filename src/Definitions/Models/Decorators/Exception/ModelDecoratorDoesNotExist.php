<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\Exceptions;

final class ModelDecoratorDoesNotExist extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('The supplied model decorator "%s" does not exist', $name));
    }
}
