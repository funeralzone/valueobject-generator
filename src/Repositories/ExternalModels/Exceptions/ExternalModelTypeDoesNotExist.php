<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\Exceptions;

final class ExternalModelTypeDoesNotExist extends \Exception
{
    public function __construct(string $type)
    {
        parent::__construct('The external model type "%s" does not exist', $type);
    }
}
