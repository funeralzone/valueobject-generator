<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Events;

use Countable;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions\InvalidModel;

final class EventSet implements Countable
{
    private $events;

    public function __construct(array $events)
    {
        $this->validateInput($events);
        $this->events = $events;
    }

    public function all(): array
    {
        return $this->events;
    }

    public function count()
    {
        return count($this->events);
    }

    private function validateInput(array $events): void
    {
        foreach ($events as $model) {
            if (! $model instanceof Event) {
                throw new InvalidModel;
            }
        }
    }
}
