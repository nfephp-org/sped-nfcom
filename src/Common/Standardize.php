<?php

namespace NFePHP\NFCom\Common;

use NFePHP\Common\Validator;
use NFePHP\NFCom\Exception\DocumentsException;
use stdClass;

class Standardize
{
    private $xml = '';
    private string $node = '';
    private string $json = '';
    public string $key = '';
    private object $sxml;
    public array $rootTagList = [
        'retNFCom',
        'retEventoNFCom',
        'eventoNFCom',
        'ConsCad',
        'consSitNFCom',
        'NFCom',
        'consStatServNFCom',
        'retEventoNFCom',
        'retConsSitNFCom',
        'retConsStatServNFCom',
        'procEventoNFCom',
        'procNFCom',
    ];

    /**
     * Constructor
     */
    public function __construct(?string $xml = null)
    {
        if (!empty($xml)) {
            $this->xml = $xml;
        }
    }

    /**
     * Identify node and extract from XML for convertion type
     * @param string|null $xml
     * @return string
     * @throws DocumentsException
     */
    public function whichIs(?string $xml = null): string
    {
        if (empty($xml) && empty($this->xml)) {
            throw new DocumentsException("O XML está vazio.");
        }
        if (!empty($xml)) {
            $this->xml = $xml;
        }
        if (!Validator::isXML($this->xml)) {
            //invalid document is not a XML
            throw new DocumentsException('Documento inválido');
        }
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($this->xml);
        foreach ($this->rootTagList as $key) {
            $node = !empty($dom->getElementsByTagName($key)->item(0))
                ? $dom->getElementsByTagName($key)->item(0)
                : '';
            if (!empty($node)) {
                $this->node = $dom->saveXML($node);
                return $key;
            }
        }
        $result = $dom->getElementsByTagName('nfcomResultMsg')->item(0);
        if (!empty($result)) {
            $cont = $result->textContent;
            if (empty($cont)) {
                throw new DocumentsException('O retorno da SEFAZ veio em BRANCO, '
                    . 'ou seja devido a um erro ou instabilidade na própria SEFAZ.');
            }
        }
        //documento does not belong to the SPED-NFCom project
        throw DocumentsException::wrongDocument(7);
    }

    /**
     * Returns extract node from XML
     */
    public function __toString(): string
    {
        return $this->node;
    }

    /**
     * Returns stdClass converted from xml
     * @param string|null $xml
     * @return stdClass
     * @throws DocumentsException
     */
    public function toStd(string $xml = null): stdClass
    {
        if (empty($xml) && empty($this->xml)) {
            throw new DocumentsException("O XML está vazio.");
        }
        if (!empty($xml)) {
            $this->xml = $xml;
        }
        $this->key = $this->whichIs();
        $this->sxml = simplexml_load_string($this->node);
        $this->json = str_replace(
            '@attributes',
            'attributes',
            json_encode($this->sxml, JSON_PRETTY_PRINT)
        );
        $std = json_decode($this->json);
        if (isset($std->infNFComSupl)) {
            $resp = $this->getQRCode();
            $std->infNFComSupl->qrCodNFCom = $resp['qrCode'];
            $this->json = json_encode($std);
        }
        if (!is_object($std)) {
            //não é um objeto entao algum erro ocorreu
            throw new DocumentsException("Falhou a converção para stdClass. Documento: $xml");
        }
        return $std;
    }

    /**
     * Returns the SimpleXml Object
     * @param string|null $xml
     * @return object
     * @throws DocumentsException
     */
    public function simpleXml(string $xml = null): object
    {
        $this->checkXml($xml);
        return $this->sxml;
    }

    /**
     * Returns JSON string form XML
     * @param string|null $xml
     * @return string
     * @throws DocumentsException
     */
    public function toJson(string $xml = null): string
    {
        $this->checkXml($xml);
        return $this->json;
    }

    /**
     * Returns array from XML
     * @param string|null $xml
     * @return array
     * @throws DocumentsException
     */
    public function toArray(string $xml = null): array
    {
        $this->checkXml($xml);
        return json_decode($this->json, true);
    }

    /**
     * Return QRCODE from XML
     * @return array
     * @throws DocumentsException
     */
    private function getQRCode(): array
    {
        if (empty($this->node)) {
            throw new DocumentsException("O XML está vazio.");
        }
        $resp = [
            'qrCode' => '',
        ];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($this->node);
        $node = $dom->getElementsByTagName('infNFComSupl')->item(0);
        if (!empty($node)) {
            $resp = [
                'qrCode' => $node->getElementsByTagName('qrCodNFCom')->item(0)->nodeValue,
            ];
        }
        return $resp;
    }

    /**
     * Check and load XML
     * @param string|null $xml
     * @return void
     * @throws DocumentsException
     */
    private function checkXml(string $xml = null)
    {
        if (empty($xml) && empty($this->xml)) {
            throw new DocumentsException("O XML está vazio.");
        }
        if (!empty($xml)) {
            $this->xml = $xml;
        }
        $this->toStd();
    }
}
