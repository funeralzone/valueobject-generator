<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Commands;

use Countable;
use Funeralzone\ValueObjectGenerator\Definitions\Commands\Exceptions\InvalidCommand;

final class CommandSet implements Countable
{
    private $commands;

    public function __construct(array $commands)
    {
        $this->validateInput($commands);
        $this->commands = $commands;
    }

    public function all(): array
    {
        return $this->commands;
    }

    public function count()
    {
        return count($this->commands);
    }

    private function validateInput(array $commands): void
    {
        foreach ($commands as $model) {
            if (! $model instanceof Command) {
                throw new InvalidCommand;
            }
        }
    }
}
