<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

interface OutputWriter
{
    public function write(string $name, string $content);
}
