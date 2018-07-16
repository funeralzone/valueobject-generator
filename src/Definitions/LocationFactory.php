<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

final class LocationFactory
{
    public function makeFromString(string $path): Location
    {
        $elements = explode('\\', $path);
        $className = array_pop($elements);
        return new Location(
            $elements,
            [],
            $className
        );
    }
}
