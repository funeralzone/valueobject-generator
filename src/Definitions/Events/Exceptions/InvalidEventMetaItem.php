<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions;

final class InvalidEventMetaItem extends \Exception
{
    public function __construct()
    {
        parent::__construct(sprintf('The supplied event meta item is not valid'));
    }
}
