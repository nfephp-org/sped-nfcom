<?php

namespace NFePHP\NFCom\Common;

use SimpleXMLElement;

class Webservices
{
    public $json;
    public $std;

    /**
     * Constructor
     * @param string $xml path or xml content from
     *               nfcom_mod62
     */
    public function __construct(string $xml)
    {
        $this->toStd($xml);
    }

    /**
     * Gets webservices parameters for specific conditions
     * @param int|string $amb 1-Produção ou 2-Homologação
     * @param int $modelo "62"
     * @see storage/autorizadores.json
     */
    public function get(string $sigla, $amb, int $modelo): \stdClass
    {
        $auto = self::getAuth($sigla, $modelo);
        if (empty($auto) || empty($this->std)) {
            throw new \RuntimeException('Falhou autorizador, parece vazio');
        }
        if (empty($this->std->$auto)) {
            throw new \RuntimeException("Nao existem webservices cadastrados para [$sigla] no modelo [$modelo]");
        }
        $ambiente = $amb == 1 ? 'producao' : 'homologacao';
        return $this->std->$auto->$ambiente;
    }

    /**
     * Obtem a sigla do autorizador para um estado origem e modelo de documento fiscal
     * @param string $sigla
     * @param int $modelo
     * @return string
     */
    public static function getAuth(string $sigla, int $modelo): string
    {
        $autfile = realpath(__DIR__ . '/../../storage/autorizadores.json');
        $autorizadores = json_decode(file_get_contents($autfile), true);
        if (!key_exists($sigla, $autorizadores[$modelo])) {
            throw new \RuntimeException("Nao existe autorizador [$sigla] para os webservices do modelo [$modelo]");
        }
        return $autorizadores[$modelo][$sigla];
    }

    /**
     * Return WS parameters in a stdClass
     */
    public function toStd(string $xml = ''): \stdClass
    {
        if (!empty($xml)) {
            $this->convert($xml);
        }
        return $this->std;
    }

    /**
     * Return WS parameters in json format
     */
    public function __toString(): string
    {
        return (string) $this->json;
    }

    /**
     * Read WS xml and convert to json and stdClass
     */
    protected function convert(string $xml)
    {
        $resp = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NOCDATA);
        $aWS = [];
        foreach ($resp->children() as $element) {
            $sigla = (string) $element->sigla;
            $aWS[$sigla] = [];
            if (isset($element->homologacao)) {
                $aWS[$sigla] += $this->extract($element->homologacao, 'homologacao');
            }
            if (isset($element->producao)) {
                $aWS[$sigla] += $this->extract($element->producao, 'producao');
            }
        }
        $this->json = json_encode($aWS);
        $this->std = json_decode(json_encode($aWS));
    }

    /**
     * Extract data from wbservices XML strorage to a array
     */
    protected function extract(SimpleXMLElement $node, string $environment): array
    {
        $amb = [];
        $amb[$environment] = [];
        foreach ($node->children() as $children) {
            $name = (string) $children->getName();
            $method = (string) $children['method'];
            $operation = (string) $children['operation'];
            $version = (string) $children['version'];
            $url = (string) $children[0];
            $operations = [
                'method' => $method,
                'operation' => $operation,
                'version' => $version,
                'url' => $url
            ];
            $amb[$environment][$name] = $operations;
        }
        return $amb;
    }
}
