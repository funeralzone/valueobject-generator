<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions;

final class TemplateRepositorySourceDoesNotExist extends \Exception
{
    public function __construct(string $source)
    {
        parent::__construct(sprintf('The template repository source "%s" does not exist', $source));
    }
}
