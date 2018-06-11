<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

interface OutputFormatter
{
    public function format(): void;
}
