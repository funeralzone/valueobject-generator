<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Twig;

use Twig_Environment;

interface TwigEnvironmentFactory
{
    public function make(): Twig_Environment;
}
