<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Twig;

use Funeralzone\ValueObjectGenerator\Output\Twig\Filters\TwigFilter;
use Funeralzone\ValueObjectGenerator\Output\Twig\Filters\TwigFilterSet;
use Twig_Environment;
use Twig_Extension_Debug;

class DefaultTwigEnvironmentExtender implements TwigEnvironmentExtender
{
    private $twigFilterSet;

    public function __construct(
        TwigFilterSet $twigFilterSet
    ) {
        $this->twigFilterSet = $twigFilterSet;
    }

    public function extend(Twig_Environment $environment): void
    {
        $this->addExtensions($environment);
        $this->extendFilters($environment);
    }

    private function addExtensions(Twig_Environment $environment): void
    {
        $environment->addExtension(new Twig_Extension_Debug);
        return;
    }

    private function extendFilters(Twig_Environment $environment): void
    {
        foreach ($this->twigFilterSet->all() as $filter) {
            /** @var TwigFilter $filter */
            $environment->addFilter(new \Twig_Filter($filter->getFilterName(), function () use ($filter) {
                return $filter->filter(func_get_args());
            }));
        }

        return;
    }
}
