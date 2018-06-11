<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\Exceptions;

final class InvalidExternalModelSupplied extends \Exception
{
    public function __construct()
    {
        parent::__construct('An invalid external model type has been supplied');
    }
}
