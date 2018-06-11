<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes;

use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\Exceptions\InvalidModelTypeRepositorySupplied;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\Exceptions\ModelTypeDoesNotExist;

class ModelTypeRepositoryGroup implements ModelTypeRepository
{
    private $repositories = [];

    public function __construct(array $repositories)
    {
        foreach ($repositories as $repository) {
            if (! $repository instanceof ModelTypeRepository) {
                throw new InvalidModelTypeRepositorySupplied();
            }
        }

        $this->repositories = $repositories;
    }

    public function has(string $type): bool
    {
        $has = false;
        foreach ($this->repositories as $repository) {
            /** @var ModelTypeRepository $repository */

            if ($repository->has($type)) {
                $has = true;
                break;
            }
        }
        return $has;
    }

    public function get(string $type): ModelType
    {
        $modelType = null;
        foreach ($this->repositories as $repository) {
            /** @var ModelTypeRepository $repository */

            if ($repository->has($type)) {
                $modelType = $repository->get($type);
                break;
            }
        }

        if ($modelType !== null) {
            return $modelType;
        } else {
            throw new ModelTypeDoesNotExist($type);
        }
    }

    public function all(): array
    {
        $modelTypes = [];
        foreach ($this->repositories as $repository) {
            /** @var ModelTypeRepository $repository */
            foreach ($repository->all() as $modelType) {
                $modelTypes[] = $modelType;
            }
        }
        return $modelTypes;
    }
}
