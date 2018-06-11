<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators;

interface ModelDecorator
{
    public function name(): string;
    public function constructorInputValidationCallable(): string;
}
