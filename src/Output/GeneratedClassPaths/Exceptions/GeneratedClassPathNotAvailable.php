<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\GeneratedClassPaths\Exceptions;

final class GeneratedClassPathNotAvailable extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('A "%s" path is not available', $name));
    }
}
