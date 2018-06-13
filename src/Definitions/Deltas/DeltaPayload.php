<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Deltas;

use Countable;
use Funeralzone\ValueObjectGenerator\Definitions\Deltas\Exceptions\InvalidDeltaPayloadItem;

final class DeltaPayload implements Countable
{
    private $payload;

    public function __construct(array $deltas)
    {
        $this->validateInput($deltas);
        $this->payload = $deltas;
    }

    public function all(): array
    {
        return $this->payload;
    }

    public function count()
    {
        return count($this->payload);
    }

    private function validateInput(array $deltas): void
    {
        foreach ($deltas as $model) {
            if (! $model instanceof DeltaPayloadItem) {
                throw new InvalidDeltaPayloadItem;
            }
        }
    }
}
