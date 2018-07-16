<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Location;

class ModelDecorator
{
    private $location;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function location(): Location
    {
        return $this->location;
    }
}
