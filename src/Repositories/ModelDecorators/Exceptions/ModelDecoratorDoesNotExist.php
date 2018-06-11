<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\Exceptions;

final class ModelDecoratorDoesNotExist extends \Exception
{
    public function __construct(string $type)
    {
        parent::__construct('The model type decorator "%s" does not exist', $type);
    }
}
