<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

class StdOutDefinitionErrorRenderer implements DefinitionErrorRenderer
{
    public function render(array $errors): void
    {
        // TODO - style the output

        print "Errors:\n";
        print "\n";
        foreach ($errors as $error) {
            print "\t$error\n";
        }
        print "\n";
    }
}
