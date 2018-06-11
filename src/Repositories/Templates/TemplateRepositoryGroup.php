<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\Templates;

use Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions\InvalidTemplateRepositorySupplied;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions\TemplateDoesNotExist;

final class TemplateRepositoryGroup implements TemplateRepository
{
    private $repositories = [];

    public function __construct(array $repositories)
    {
        foreach ($repositories as $repository) {
            if (! $repository instanceof TemplateRepository) {
                throw new InvalidTemplateRepositorySupplied;
            }
        }

        $this->repositories = $repositories;
    }

    public function has(string $type): bool
    {
        $has = false;
        foreach ($this->repositories as $repository) {
            /** @var TemplateRepository $repository */

            if ($repository->has($type)) {
                $has = true;
                break;
            }
        }
        return $has;
    }

    public function get(string $type): Template
    {
        $template = null;
        foreach ($this->repositories as $repository) {
            /** @var TemplateRepository $repository */

            if ($repository->has($type)) {
                $template = $repository->get($type);
                break;
            }
        }

        if ($template !== null) {
            return $template;
        } else {
            throw new TemplateDoesNotExist($type);
        }
    }
}
