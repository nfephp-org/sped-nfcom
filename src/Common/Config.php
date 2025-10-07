<?php

namespace NFePHP\NFCom\Common;

use JsonSchema\Validator as JsonValid;
use NFePHP\NFCom\Exception\DocumentsException;

class Config
{
    /**
     * Validate method
     * @param string $content config.json
     */
    public static function validate(string $content): \stdClass
    {
        $std = json_decode($content);
        if (!is_object($std)) {
            throw DocumentsException::wrongDocument(8, "Não foi passado um json valido.");
        }
        self::validInputData($std);
        return $std;
    }

    /**
     * Validation with JsonValid::class
     * @throws DocumentsException
     */
    protected static function validInputData(\stdClass $data): bool
    {
        $filejsonschema = __DIR__ . "/../../storage/config.schema";
        $validator = new JsonValid();
        $validator->check($data, (object)['$ref' => 'file://' . $filejsonschema]);
        if (!$validator->isValid()) {
            $msg = "";
            foreach ($validator->getErrors() as $error) {
                $msg .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw DocumentsException::wrongDocument(8, $msg);
        }
        return true;
    }
}
