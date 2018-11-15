<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Conventions\ModelNamer;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelTypeRepository;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\TemplateRepository;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\TemplateRepositoryGroup;
use Twig_Environment;
use Twig_Filter;
use Twig_Function;

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
        if (!$this->twigEnvironment) {
            $loader = new TwigTemplateLoader($this->buildTemplateRepository());
            $this->twigEnvironment = new Twig_Environment(
                $loader,
                []
            );

            $this->extendTwig($this->twigEnvironment);
        }
        return $this->twigEnvironment;
    }

    private function extendTwig(Twig_Environment $environment): void
    {
        $environment->addFilter(new Twig_Filter('ucFirst', function ($input) {
            if (is_string($input)) {
                return ucfirst($input);
            } else {
                return $input;
            }
        }));

        $environment->addFunction(new Twig_Function('makeNonNullModelName', function ($input) {
            $modelNamer = new ModelNamer();
            if ($input instanceof Model) {
                /** @var Model $input */
                return $modelNamer->makeNonNullClassName($input->definitionName());
            } else {
                return $input;
            }
        }));

        $environment->addFilter(new Twig_Filter('removeDuplicateLines', function ($input) {
            $uniqueLines = [];
            $uniqueLinesForComparison = [];
            foreach (explode("\n", $input) as $line) {
                $lineToCompare = trim($line);

                if (in_array($lineToCompare, $uniqueLinesForComparison) === false) {
                    $uniqueLines[] = $line;
                    $uniqueLinesForComparison[] = $lineToCompare;
                }
            }

            return implode("\n", $uniqueLines);
        }));
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
