<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\Templates;

use Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions\TemplateDoesNotExist;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions\TemplateRepositorySourceDoesNotExist;

final class FileSystemTemplateRepository implements TemplateRepository
{
    private $rootFolderPath;

    public function __construct(string $rootFolderPath)
    {
        if (is_dir($rootFolderPath)) {
            $this->rootFolderPath = rtrim($rootFolderPath, '/') . '/';
        } else {
            throw new TemplateRepositorySourceDoesNotExist($rootFolderPath);
        }
    }

    public function has(string $item): bool
    {
        return is_file($this->makeItemPath($item));
    }

    public function get(string $item): Template
    {
        if ($this->has($item)) {
            $contents = file_get_contents($this->makeItemPath($item));
            return new Template($item, $contents);
        } else {
            throw new TemplateDoesNotExist($item);
        }
    }

    private function makeItemPath(string $item): string
    {
        return $this->rootFolderPath.$item;
    }
}
