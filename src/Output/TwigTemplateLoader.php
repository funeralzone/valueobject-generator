<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Repositories\Templates\TemplateRepository;
use Twig_Error_Loader;
use Twig_ExistsLoaderInterface;
use Twig_LoaderInterface;
use Twig_Source;
use Twig_SourceContextLoaderInterface;

final class TwigTemplateLoader implements
    Twig_LoaderInterface,
    Twig_ExistsLoaderInterface,
    Twig_SourceContextLoaderInterface
{
    private $templateRepository;

    public function __construct(TemplateRepository $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    public function getSourceContext($name)
    {
        $name = (string)$name;
        if (!$this->exists($name)) {
            throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
        }

        return new Twig_Source($this->templateRepository->get($name), $name);
    }

    public function exists($name)
    {
        return $this->templateRepository->has($name);
    }

    public function getCacheKey($name)
    {
        if (!$this->exists($name)) {
            throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
        }

        return $name . ':' . $this->templateRepository->get($name);
    }

    public function isFresh($name, $time)
    {
        if (!$this->exists($name)) {
            throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
        }

        return true;
    }
}
