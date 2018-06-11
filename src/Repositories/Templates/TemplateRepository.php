<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\Templates;

interface TemplateRepository
{
    public function get(string $item): Template;
    public function has(string $item): bool;
}
