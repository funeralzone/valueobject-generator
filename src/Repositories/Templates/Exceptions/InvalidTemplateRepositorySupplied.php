<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions;

final class InvalidTemplateRepositorySupplied extends \Exception
{
    public function __construct()
    {
        parent::__construct('An invalid template repository has been supplied');
    }
}
