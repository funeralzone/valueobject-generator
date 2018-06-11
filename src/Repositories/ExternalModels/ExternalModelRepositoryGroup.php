<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ExternalModels;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\Exceptions\ExternalModelTypeDoesNotExist;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\Exceptions\InvalidModelTypeRepositorySupplied;

final class ExternalModelRepositoryGroup implements ExternalModelRepository
{
    private $repositories = [];

    public function __construct(array $repositories)
    {
        foreach ($repositories as $repository) {
            if (! $repository instanceof ExternalModelRepository) {
                throw new InvalidModelTypeRepositorySupplied;
            }
        }

        $this->repositories = $repositories;
    }

    public function has(string $type): bool
    {
        $has = false;
        foreach ($this->repositories as $repository) {
            /** @var ExternalModelRepository $repository */

            if ($repository->has($type)) {
                $has = true;
                break;
            }
        }
        return $has;
    }

    public function get(string $type): Model
    {
        $externalModel = null;
        foreach ($this->repositories as $repository) {
            /** @var ExternalModelRepository $repository */

            if ($repository->has($type)) {
                $externalModel = $repository->get($type);
                break;
            }
        }

        if ($externalModel !== null) {
            return $externalModel;
        } else {
            throw new ExternalModelTypeDoesNotExist($type);
        }
    }
}
