<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\GeneratedClassPaths;

use Funeralzone\ValueObjectGenerator\Output\GeneratedClassPaths\Exceptions\GeneratedClassPathNotAvailable;
use Funeralzone\ValueObjectGenerator\Output\GeneratedClassPaths\Exceptions\InvalidGeneratedPathWasSupplied;

final class GeneratedClassPaths
{
    private $paths;

    public function __construct(array $paths)
    {
        $indexedPaths = [];
        foreach ($paths as $path) {
            if (!$path instanceof GeneratedClassPath) {
                throw new InvalidGeneratedPathWasSupplied;
            }
            $indexedPaths[$path->getType()->toNative()] = $path;
        }

        $this->paths = $indexedPaths;
    }

    public function all(): array
    {
        return $this->paths;
    }

    public function has(string $name): ?bool
    {
        return array_key_exists($name, $this->paths);
    }

    public function get(string $name): GeneratedClassPath
    {
        if (!$this->has($name)) {
            throw new GeneratedClassPathNotAvailable($name);
        }

        return $this->paths[$name];
    }
}
