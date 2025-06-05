<?php

namespace NFePHP\NFCom\Common;

use stdClass;
use NFePHP\Common\Strings;

class Aux
{
    /**
     * Formatação numerica condicional
     * @param string|float|int|null $value
     */
    public static function conditionalNumberFormatting($value = null, int $decimal = 2): ?string
    {
        if (is_numeric($value)) {
            return number_format($value, $decimal, '.', '');
        }
        return null;
    }

    /**
     * Includes missing or unsupported properties in stdClass
     * Replace all unsuported chars
     */
    public static function equilizeParameters(stdClass $std, array $possible, $replaceAccentedChars = false): stdClass
    {
        return Strings::equilizeParameters(
            $std,
            $possible,
            $replaceAccentedChars
        );
    }
}
