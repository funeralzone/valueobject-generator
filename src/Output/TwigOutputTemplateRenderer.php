<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Output;

use Funeralzone\ValueObjectGenerator\Output\Twig\DefaultTwigEnvironmentFactory;
use Twig_Environment;

final class TwigOutputTemplateRenderer implements OutputTemplateRenderer
{
//    private const TAB_LENGTH_IN_SPACES = 4;
//
//    private $templateRepository;

    private $twigEnvironment;
//
//    public function __construct(TemplateRepository $templateRepository = null)
//    {
//        $this->templateRepository = $templateRepository;
//    }

    private $twigEnvironmentFactory;

    public function __construct(DefaultTwigEnvironmentFactory $twigEnvironmentFactory)
    {
        $this->twigEnvironmentFactory = $twigEnvironmentFactory;
    }

    public function render(string $templateName, array $templateVariables)
    {
        $twigEnvironment = $this->twigEnvironment();
        return $twigEnvironment->render($templateName, $templateVariables);
    }

    private function twigEnvironment(): Twig_Environment
    {
        if (!$this->twigEnvironment) {
            $this->twigEnvironment = $this->twigEnvironmentFactory->make();
        }
        return $this->twigEnvironment;
    }

//    private function extendTwig(Twig_Environment $environment): void
//    {
//        $environment->addExtension(new Twig_Extension_Debug());

//        $environment->addFilter(new Twig_Filter('ucFirst', function ($input) {
//            if (is_string($input)) {
//                return ucfirst($input);
//            } else {
//                return $input;
//            }
//        }));

//        $environment->addFilter(new Twig_Filter('removeEmptyLines', function ($input) {
//            $lines = [];
//            foreach (explode("\n", $input) as $line) {
//                if ($line !== '') {
//                    $lines[] = $line;
//                }
//            }
//            return implode("\n", $lines);
//        }));

//        $environment->addFilter(new Twig_Filter('trimByLine', function ($input, string $append = '') {
//            $trimmedLines = [];
//            foreach (explode("\n", $input) as $line) {
//                $trimmedLines[] = $append.trim($line);
//            }
//            return implode("\n", $trimmedLines);
//        }));

//        $environment->addFilter(new Twig_Filter('tabInByLine', function ($input, int $depth = 1) {
//            $lines = [];
//            $tabRepresentation = str_repeat(' ', self::TAB_LENGTH_IN_SPACES);
//            foreach (explode("\n", $input) as $line) {
//                if ($line === '') {
//                    $lines[] = $line;
//                } else {
//                    $lines[] = str_repeat($tabRepresentation, $depth).$line;
//                }
//            }
//            return implode("\n", $lines);
//        }));
//
//        $environment->addFilter(new Twig_Filter('removeIndentationByBlock', function ($input, $halt = false) {
//            $nestedCount = 0;
//            $characterCountToTrim = 0;
//            $outputLines = [];
//            foreach (explode("\n", $input) as $line) {
//                if ($nestedCount) {
//                    $trimmedLine = ltrim($line);
//                    if (substr($trimmedLine, 0, 1) === '}') {
//                        $nestedCount--;
//                    }
//                } else {
//                    $lineLength = strlen($line);
//                    for ($index = 0; $index < $lineLength; $index++) {
//                        if ($line[$index] !== ' ') {
//                            break;
//                        }
//                    }
//                    $characterCountToTrim = $index;
//
//                    $trimmedLine = rtrim($line);
//                    if (substr($trimmedLine, strlen($trimmedLine) - 1) === '{') {
//                        $nestedCount++;
//                    }
//                }
//
//                if ($characterCountToTrim > 0) {
//                    $textToTrim = substr($line, 0, $characterCountToTrim);
//                    if (preg_match('/^[ ]*$/', $textToTrim)) {
//                        $line = substr($line, $characterCountToTrim);
//                    }
//                }
//
//                $outputLines[] = $line;
//            }
//
//            return implode("\n", $outputLines);
//        }));

//        $environment->addFilter(new Twig_Filter('removeDuplicateEmptyLines', function ($input) {
//            $lines = [];
//            $previousLineWasBlank = false;
//            foreach (explode("\n", $input) as $line) {
//                if ($line === '') {
//                    if ($previousLineWasBlank === false) {
//                        $lines[] = $line;
//                    }
//                    $previousLineWasBlank = true;
//                } else {
//                    $lines[] = $line;
//                    $previousLineWasBlank = false;
//                }
//            }
//            return implode("\n", $lines);
//        }));

//        $environment->addFilter(new Twig_Filter('prependEmptyLine', function ($input) {
//            if ($input === '') {
//                return $input;
//            } else {
//                return "\n\n".$input;
//            }
//        }));
//        $environment->addFilter(new Twig_Filter('appendEmptyLine', function ($input) {
//            if ($input === '') {
//                return $input;
//            } else {
//                return $input."\n\n";
//            }
//        }));
//    }
}
