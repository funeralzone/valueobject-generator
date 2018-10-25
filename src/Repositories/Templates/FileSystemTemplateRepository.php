<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\Templates;

use Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions\TemplateDoesNotExist;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\Exceptions\TemplateRepositorySourceDoesNotExist;

final class FileSystemTemplateRepository implements TemplateRepository
{
    private const DIRECTORY_DELIMITER = '.';

    private $rootFolderPath;
    private $templateCache = [];

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
        if (! $this->has($item)) {
            throw new TemplateDoesNotExist($item);
        }

        $contents = $this->loadTemplateContents($item);
        return new Template($item, $contents);
    }

    private function makeItemPath(string $item): string
    {
        $normalisedItem = str_replace('.twig', '', $item);

        $itemElements = explode(self::DIRECTORY_DELIMITER, $normalisedItem);
        $fileName = array_pop($itemElements).'.twig';
        $itemElements[] = $fileName;

        return $this->rootFolderPath.implode('/', $itemElements);
    }

    private function loadTemplateContents(string $item): string
    {
        if (array_key_exists($item, $this->templateCache)) {
            return $this->templateCache[$item];
        }

        $itemPath = $this->makeItemPath($item);
        $contents = file_get_contents($this->makeItemPath($item));
        $this->templateCache[$item] = $contents;

        return $contents;
    }
}
