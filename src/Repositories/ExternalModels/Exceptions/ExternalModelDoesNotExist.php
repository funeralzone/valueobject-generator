<?php
declare(strict_types=1);


namespace Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\Exceptions;

final class ExternalModelDoesNotExist extends \Exception
{
    public function __construct(string $type)
    {
        parent::__construct(sprintf('The external model "%s" does not exist', $type));
    }
}
