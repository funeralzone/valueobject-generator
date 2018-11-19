<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Twig\Filters;

use Funeralzone\ValueObjectGenerator\Output\Twig\Filters\Exceptions\InvalidTwigFilterWasSupplied;

final class TwigFilterSet
{
    private $filters;

    public function __construct(array $filters)
    {
        foreach ($filters as $item) {
            if (! $item instanceof TwigFilter) {
                throw new InvalidTwigFilterWasSupplied();
            }
        }
        
        $this->filters = $filters;
    }

    public function all(): array
    {
        return $this->filters;
    }
}
