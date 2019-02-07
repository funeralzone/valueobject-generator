<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators;

use Funeralzone\ValueObjects\Enums\EnumTrait;
use Funeralzone\ValueObjects\ValueObject;

/**
 * @static ModelDecoratorHookStage PRE_ORIGINAL_CODE
 * @static ModelDecoratorHookStage POST_ORIGINAL_CODE
 */

final class ModelDecoratorHookStage implements ValueObject
{
    use EnumTrait;

    const PRE_ORIGINAL_CODE = 1;
    const POST_ORIGINAL_CODE = 2;
}
