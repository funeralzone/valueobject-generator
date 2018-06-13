<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Conventions;

final class ModelNamer
{
    const TEST_SUFFIX = 'Test';
    const FILE_NAME_SUFFIX = '.php';

    public function makeNullableImplementationInterfaceName(string $item): string
    {
        return $item.'Interface';
    }

    public function makeNonNullClassName(string $item): string
    {
        return 'NonNull'.$item;
    }

    public function makeNullClassName(string $item): string
    {
        return 'Null'.$item;
    }

    public function makeNullableClassName(string $item): string
    {
        return $item;
    }

    public function makeRequiredClassName(string $item): string
    {
        return $item;
    }

    public function makeNonNullTestClassName(string $item): string
    {
        return $this->makeNonNullClassName($item).self::TEST_SUFFIX;
    }

    public function makeNullTestClassName(string $item): string
    {
        return $this->makeNullClassName($item).self::TEST_SUFFIX;
    }

    public function makeNullableTestClassName(string $item): string
    {
        return $this->makeNullableClassName($item).self::TEST_SUFFIX;
    }

    public function makeRequiredTestClassName(string $item): string
    {
        return $this->makeRequiredClassName($item).self::TEST_SUFFIX;
    }

    public function makeNullableImplementationInterfaceFileName(string $item): string
    {
        return $this->makeNullableImplementationInterfaceName($item).self::FILE_NAME_SUFFIX;
    }

    public function makeNonNullClassFileName(string $item): string
    {
        return $this->makeNonNullClassName($item).self::FILE_NAME_SUFFIX;
    }

    public function makeNullClassFileName(string $item): string
    {
        return $this->makeNullClassName($item).self::FILE_NAME_SUFFIX;
    }

    public function makeNullableClassFileName(string $item): string
    {
        return $this->makeNullableClassName($item).self::FILE_NAME_SUFFIX;
    }

    public function makeRequiredClassFileName(string $item): string
    {
        return $this->makeRequiredClassName($item).self::FILE_NAME_SUFFIX;
    }

    public function makeNonNullTestClassFileName(string $item): string
    {
        return $this->makeNonNullTestClassName($item).self::FILE_NAME_SUFFIX;
    }

    public function makeNullTestClassFileName(string $item): string
    {
        return $this->makeNullTestClassName($item).self::FILE_NAME_SUFFIX;
    }

    public function makeNullableTestClassFileName(string $item): string
    {
        return $this->makeNullableTestClassName($item).self::FILE_NAME_SUFFIX;
    }

    public function makeRequiredTestClassFileName(string $item): string
    {
        return $this->makeRequiredTestClassName($item).self::FILE_NAME_SUFFIX;
    }
}
