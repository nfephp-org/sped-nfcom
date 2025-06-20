<?php

namespace NFePHP\NFCom\Common;

class Keys
{
    /**
     * Build 44 digits keys to NFCom
     * @param string $cUF UF number
     * @param string $ano year
     * @param string $mes month
     * @param string $cnpj
     * @param string $mod model of document 62
     * @param string $serie
     * @param string $numero document number
     * @param string $tpEmis emission type
     * @param string $nSiteAutoriz Site autorizador
     * @param string $codigo random number or document number
     * @return string
     */
    public static function build(
        string $cUF,
        string $ano,
        string $mes,
        string $cnpj,
        string $mod,
        string $serie,
        string $numero,
        string $tpEmis,
        string $nSiteAutoriz,
        string $codigo = null
    ) {
        if (empty($codigo)) {
            $codigo = self::random();
        }
        $format = "%02d%02d%02d%s%02d%03d%09d%01d%01d%07d";
        $key = sprintf(
            $format,
            $cUF,
            $ano,
            $mes,
            $cnpj,
            $mod,
            $serie,
            $numero,
            $tpEmis,
            $nSiteAutoriz,
            $codigo
        );
        return $key . self::verifyingDigit($key);
    }

    /**
     * Verifies that the key provided is valid
     * @param string $key
     * @return boolean
     */
    public static function isValid($key)
    {
        if (strlen($key) != 44) {
            return false;
        }
        $cDV = substr($key, -1);
        $calcDV = self::verifyingDigit(substr($key, 0, 43));
        if ($cDV === $calcDV) {
            return true;
        }
        return false;
    }

    /**
     * This method calculates verifying digit
     * @param string $key
     * @return string
     */
    public static function verifyingDigit($key)
    {
        if (strlen($key) != 43) {
            return '';
        }
        $multipliers = [2, 3, 4, 5, 6, 7, 8, 9];
        $iCount = 42;
        $weightedSum = 0;
        while ($iCount >= 0) {
            for ($mCount = 0; $mCount < 8 && $iCount >= 0; $mCount++) {
                $sub = (int) $key[$iCount];
                $weightedSum +=  $sub * $multipliers[$mCount];
                $iCount--;
            }
        }
        $vdigit = 11 - ($weightedSum % 11);
        if ($vdigit > 9) {
            $vdigit = 0;
        }
        return (string) $vdigit;
    }

    /**
     * Generate and return a 8 digits random number
     * for cNF tag
     * @param string|null $nnf
     * @return string
     */
    public static function random($nnf = null)
    {
        $loop = true;
        while ($loop) {
            $cnf = str_pad((string)mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $loop = !self::cNFIsValid($cnf);
            if (!empty($nnf)) {
                if ((int)$cnf === (int)$nnf) {
                    $loop = true;
                }
            }
        }
        return $cnf;
    }

    /**
     * Verify if cNF number is valid NT2019.001
     * @param string $cnf
     */
    public static function cNFIsValid($cnf)
    {
        $defs = [
            '0000000', '1111111', '2222222', '3333333', '4444444',
            '5555555', '6666666', '7777777', '8888888', '9999999',
            '1234567', '2345678', '3456789', '4567890', '5678901',
            '6789012', '7890123', '8901234', '9012345', '0123456'
        ];
        return !in_array($cnf, $defs);
    }

    /**
     * Return elements of key
     * @param string $key
     * @return array
     */
    public static function decompile($key)
    {
        if (strlen($key) != 44) {
            return [];
        }
        return [
            'cuf'       => substr($key, 0, 2),
            'ano'       => substr($key, 2, 2),
            'mes'       => substr($key, 4, 2),
            'cnpj'      => substr($key, 6, 14),
            'modelo'    => substr($key, 20, 2),
            'serie'     => substr($key, 22, 3),
            'nnf'       => substr($key, 25, 9),
            'tpemis'    => $key[34],
            'nsite'     => $key[35],
            'cnf'       => substr($key, 36, 7),
            'dv'        => substr($key, -1)
        ];
    }
}
