<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Events;

use Countable;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions\InvalidEventPayloadItem;

final class EventPayload implements Countable
{
    private $payload;

    public function __construct(array $models)
    {
        $this->validateInput($models);
        $this->payload = $models;
    }

    public function all(): array
    {
        return $this->payload;
    }

    public function count()
    {
        return count($this->payload);
    }

    private function validateInput(array $models): void
    {
        foreach ($models as $model) {
            if (! $model instanceof EventPayloadItem) {
                throw new InvalidEventPayloadItem;
            }
        }
    }
}
