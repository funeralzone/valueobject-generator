<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Twig\Filters;

interface TwigFilter
{
    public function getFilterName(): string;
    public function filter(array $arguments): string;
}
