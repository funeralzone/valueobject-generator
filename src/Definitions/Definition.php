<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelRegister;
use Funeralzone\ValueObjectGenerator\Definitions\Models\ModelSet;

final class Definition
{
    private $modelRegister;
    private $models;

    public function __construct(
        ModelRegister $modelRegister,
        ModelSet $models
    ) {
        $this->modelRegister = $modelRegister;
        $this->models = $models;
    }

    public function modelRegister(): ModelRegister
    {
        return $this->modelRegister;
    }

    public function models(): ModelSet
    {
        return $this->models;
    }
}
