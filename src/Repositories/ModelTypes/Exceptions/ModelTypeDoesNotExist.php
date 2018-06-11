<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\Exceptions;

final class ModelTypeDoesNotExist extends \Exception
{
    public function __construct(string $type)
    {
        parent::__construct(sprintf('The model type "%s" does not exist', $type));
    }
}
