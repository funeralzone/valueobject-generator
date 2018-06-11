<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Exceptions;

final class DefinitionSourceDoesNotExist extends \Exception
{
    public function __construct(string $source)
    {
        parent::__construct(sprintf('The supplied source (%s) does not exist', $source));
    }
}
