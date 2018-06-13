<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Deltas;

use Countable;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\Exceptions\DeltaDoesNotExist;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\Exceptions\InvalidDelta;

final class DeltaSet implements Countable
{
    private $deltas;
    private $deltasByName;

    public function __construct(array $deltas)
    {
        $this->validateInput($deltas);
        $this->deltas = $deltas;
        $this->deltasByName = $this->indexDeltasByName($deltas);
    }

    public function all(): array
    {
        return $this->deltas;
    }

    public function count()
    {
        return count($this->deltas);
    }

    public function hasByName(string $name): bool
    {
        return array_key_exists($name, $this->deltasByName);
    }

    public function getByname(string $name): Delta
    {
        if ($this->hasByName($name)) {
            return $this->deltasByName[$name];
        } else {
            throw new DeltaDoesNotExist($name);
        }
    }

    private function validateInput(array $events): void
    {
        foreach ($events as $model) {
            if (! $model instanceof Delta) {
                throw new InvalidDelta;
            }
        }
    }

    private function indexDeltasByName(array $deltas): array
    {
        $indexedDeltas = [];
        foreach ($deltas as $delta) {
            /** @var Delta $delta */
            $indexedDeltas[$delta->definitionName()] = $delta;
        }
        return $indexedDeltas;
    }
}
