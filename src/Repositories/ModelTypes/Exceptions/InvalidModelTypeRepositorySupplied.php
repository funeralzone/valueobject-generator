<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\Exceptions;

final class InvalidModelTypeRepositorySupplied extends \Exception
{
    public function __construct()
    {
        parent::__construct('An invalid model type repository has been supplied');
    }
}
