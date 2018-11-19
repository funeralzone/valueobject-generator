<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Twig;

use Twig_Environment;

interface TwigEnvironmentExtender
{
    public function extend(Twig_Environment $environment): void;
}
