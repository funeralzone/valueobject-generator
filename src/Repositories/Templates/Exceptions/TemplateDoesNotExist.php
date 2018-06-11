<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions;

final class TemplateDoesNotExist extends \Exception
{
    public function __construct(string $template)
    {
        parent::__construct(sprintf('The template "%s" does not exist', $template));
    }
}
