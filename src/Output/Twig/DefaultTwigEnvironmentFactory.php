<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output\Twig;

use Funeralzone\ValueObjectGenerator\Output\TwigTemplateLoader;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\TemplateRepository;
use Twig_Environment;

final class DefaultTwigEnvironmentFactory implements TwigEnvironmentFactory
{
    private $templateRepository;
    private $twigEnvironmentExtender;

    public function __construct(
        TemplateRepository $templateRepository,
        TwigEnvironmentExtender $twigEnvironmentExtender
    ) {
        $this->templateRepository = $templateRepository;
        $this->twigEnvironmentExtender = $twigEnvironmentExtender;
    }

    public function make(): Twig_Environment
    {
        $loader = new TwigTemplateLoader($this->templateRepository);
        $environment = new Twig_Environment(
            $loader,
            []
        );

        $this->twigEnvironmentExtender->extend($environment);

        return $environment;
    }
}
