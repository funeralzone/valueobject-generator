<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjects\Scalars\StringTrait;

class ModelInterface
{
    use StringTrait;

    public function name(): string
    {
        return ltrim(strrchr($this->string, '\\'), '\\');
    }

    public function path(): string
    {
        return $this->string;
    }
}
