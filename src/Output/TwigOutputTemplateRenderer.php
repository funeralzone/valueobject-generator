<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelTypeRepository;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\TemplateRepository;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\TemplateRepositoryGroup;
use Twig_Environment;

final class TwigOutputTemplateRenderer implements OutputTemplateRenderer
{
    private $modelTypeRepository;
    private $partialsTemplateRepository;

    private $twigEnvironment;

    public function __construct(
        ModelTypeRepository $modelTypeRepository,
        TemplateRepository $partialsTemplateRepository = null
    ) {
        $this->modelTypeRepository = $modelTypeRepository;
        $this->partialsTemplateRepository = $partialsTemplateRepository;
    }

    public function render(string $templateName, array $templateVariables)
    {
        $twigEnvironment = $this->twigEnvironment();
        return $twigEnvironment->render($templateName, $templateVariables);
    }

    private function twigEnvironment(): Twig_Environment
    {
        if (! $this->twigEnvironment) {
            $loader = new TwigTemplateLoader($this->buildTemplateRepository());
            $this->twigEnvironment = new Twig_Environment(
                $loader,
                []
            );
        }
        return $this->twigEnvironment;
    }

    private function buildTemplateRepository(): TemplateRepository
    {
        $repositories = [];

        foreach ($this->modelTypeRepository->all() as $modelType) {
            /** @var ModelType $modelType */
            $repositories[] = $modelType->templateRepository();
        }

        if ($this->partialsTemplateRepository) {
            $repositories[] = $this->partialsTemplateRepository;
        }

        return new TemplateRepositoryGroup($repositories);
    }
}
