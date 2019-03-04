<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorSet;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

interface Model
{
    public function modelRegister(): ModelRegister;
    public function parent(): ?Model;
    public function namespace(): ModelNamespace;
    public function definitionName(): string;
    public function type(): ModelType;
    public function children(): ModelSet;
    public function externalToDefinition(): bool;
    public function properties(): ModelProperties;
    public function decorators(): ModelDecoratorSet;
    public function testStipulations(): ?ModelTestStipulations;
}
