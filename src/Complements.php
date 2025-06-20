<?php

namespace NFePHP\NFCom;

use NFePHP\NFCom\Common\Standardize;
use NFePHP\NFCom\Exception\DocumentsException;
use DOMDocument;

class Complements
{
    protected static $urlPortal = 'http://www.portalfiscal.inf.br/nfcom';

    /**
     * Authorize document adding his protocol
     */
    public static function toAuthorize(string $request, string $response): string
    {
        if (empty($request)) {
            throw new DocumentsException('Erro ao protocolar !! o xml '
                . 'a protocolar está vazio.');
        }
        if (empty($response)) {
            throw new DocumentsException('Erro ao protocolar !!'
                . ' O retorno da sefaz está vazio.');
        }
        $st = new Standardize();
        $key = ucfirst($st->whichIs($request));
        if ($key !== 'NFCom' && $key !== 'EnvEvento' && $key !== 'InutNFCom') {
            //wrong document, this document is not able to recieve a protocol
            throw DocumentsException::wrongDocument(0, $key);
        }
        $func = "add" . $key . "Protocol";
        return self::$func($request, $response);
    }

    /**
     * Add tags B2B, as example ANFAVEA
     * @param  string $nfcom xml nfcom string content
     * @param  string $b2b xml b2b string content
     * @param  string $tagB2B name B2B tag default 'NFComB2BFin' from ANFAVEA
     * @throws \InvalidArgumentException
     */
    public static function b2bTag(string $nfcom, string $b2b, string $tagB2B = 'NFComB2BFin'): string
    {
        $domnfcom = new DOMDocument('1.0', 'UTF-8');
        $domnfcom->preserveWhiteSpace = false;
        $domnfcom->formatOutput = false;
        $domnfcom->loadXML($nfcom);
        $nodenfcom = $domnfcom->getElementsByTagName('nfcomProc')->item(0);
        if (empty($nodenfcom)) {
            //not is NFCom or dont protocoladed doc
            throw DocumentsException::wrongDocument(1);
        }
        //carrega o arquivo B2B
        $domb2b = new DOMDocument('1.0', 'UTF-8');
        $domb2b->preserveWhiteSpace = false;
        $domb2b->formatOutput = false;
        $domb2b->loadXML($b2b);
        $nodeb2b = $domnfcom->getElementsByTagName($tagB2B)->item(0);
        if (empty($nodeb2b)) {
            //xml is not protocoladed or dont is a NFCom
            throw DocumentsException::wrongDocument(2);
        }
        //cria a NFCom processada com a tag do protocolo
        $procb2b = new DOMDocument('1.0', 'UTF-8');
        $procb2b->preserveWhiteSpace = false;
        $procb2b->formatOutput = false;
        //cria a tag nfcomProc
        $nfcomProcB2B = $procb2b->createElement('nfcomProcB2B');
        $procb2b->appendChild($nfcomProcB2B);
        //inclui a tag NFCom
        $node1 = $procb2b->importNode($nodenfcom, true);
        $nfcomProcB2B->appendChild($node1);
        //inclui a tag NFComB2BFin
        $node2 = $procb2b->importNode($nodeb2b, true);
        $nfcomProcB2B->appendChild($node2);
        $nfcomb2bXML = $procb2b->saveXML();
        $nfcomb2bXMLString = str_replace(array("\n","\r","\s"), '', $nfcomb2bXML);
        return (string) $nfcomb2bXMLString;
    }

    /**
     * Add cancel protocol to a autorized NFCom
     * if event is not a cancellation will return
     * the same autorized NFCom passing
     * NOTE: This action is not necessary, I use only for my needs to
     *       leave the NFCom marked as Canceled in order to avoid mistakes
     *       after its cancellation.
     * @param  string $nfcom content of autorized NFCom XML
     * @param  string $cancelamento content of SEFAZ response
     * @throws \InvalidArgumentException
     */
    public static function cancelRegister(string $nfcom, string $cancelamento): string
    {
        $procXML = $nfcom;
        $domnfcom = new DOMDocument('1.0', 'utf-8');
        $domnfcom->formatOutput = false;
        $domnfcom->preserveWhiteSpace = false;
        $domnfcom->loadXML($nfcom);
        $nfcomproc = $domnfcom->getElementsByTagName('nfcomProc')->item(0);
        $proNFCom = $domnfcom->getElementsByTagName('protNFCom')->item(0);
        if (empty($proNFCom)) {
            //not protocoladed NFCom
            throw DocumentsException::wrongDocument(1);
        }
        $chaveNFCom = $proNFCom->getElementsByTagName('chNFCom')->item(0)->nodeValue;
        $domcanc = new DOMDocument('1.0', 'utf-8');
        $domcanc->formatOutput = false;
        $domcanc->preserveWhiteSpace = false;
        $domcanc->loadXML($cancelamento);
        $eventos = $domcanc->getElementsByTagName('retEvento');
        foreach ($eventos as $evento) {
            $infEvento = $evento->getElementsByTagName('infEvento')->item(0);
            $cStat = $infEvento->getElementsByTagName('cStat')
                ->item(0)
                ->nodeValue;
            $nProt = $infEvento->getElementsByTagName('nProt')
                ->item(0)
                ->nodeValue;
            $chaveEvento = $infEvento->getElementsByTagName('chNFCom')
                ->item(0)
                ->nodeValue;
            $tpEvento = $infEvento->getElementsByTagName('tpEvento')
                ->item(0)
                ->nodeValue;
            if (
                in_array($cStat, ['135', '136', '155'])
                && ($tpEvento == Tools::EVT_CANCELA
                    || $tpEvento == Tools::EVT_CANCELASUBSTITUICAO
                )
                && $chaveEvento == $chaveNFCom
            ) {
                $node = $domnfcom->importNode($evento, true);
                $domnfcom->documentElement->appendChild($node);
                break;
            }
        }
        return $domnfcom->saveXML();
    }

    /**
     * Authorize Inutilization of numbers
     * @throws \InvalidArgumentException
     */
    protected static function addInutNFComProtocol(string $request, string $response): string
    {
        $req = new DOMDocument('1.0', 'UTF-8');
        $req->preserveWhiteSpace = false;
        $req->formatOutput = false;
        $req->loadXML($request);
        $inutNFCom = $req->getElementsByTagName('inutNFCom')->item(0);
        $versao = $inutNFCom->getAttribute("versao");
        $infInut = $req->getElementsByTagName('infInut')->item(0);
        $tpAmb = $infInut->getElementsByTagName('tpAmb')->item(0)->nodeValue;
        $cUF = !empty($infInut->getElementsByTagName('cUF')->item(0)->nodeValue)
            ? $infInut->getElementsByTagName('cUF')->item(0)->nodeValue : '';
        $ano = $infInut->getElementsByTagName('ano')->item(0)->nodeValue;

        // Checks if exists CNPJ tag in the XML of event, otherwise, uses the CPF tag
        $cpfOrCnpjTag = $infInut->getElementsByTagName('CNPJ')->item(0) ? 'CNPJ' : 'CPF';

        $cpfOrCnpjTagValue = $infInut->getElementsByTagName($cpfOrCnpjTag)->item(0)->nodeValue;
        $mod = $infInut->getElementsByTagName('mod')->item(0)->nodeValue;
        $serie = $infInut->getElementsByTagName('serie')->item(0)->nodeValue;
        $nNFIni = $infInut->getElementsByTagName('nNFIni')->item(0)->nodeValue;
        $nNFFin = $infInut->getElementsByTagName('nNFFin')->item(0)->nodeValue;

        $ret = new DOMDocument('1.0', 'UTF-8');
        $ret->preserveWhiteSpace = false;
        $ret->formatOutput = false;
        $ret->loadXML($response);
        $retInutNFCom = $ret->getElementsByTagName('retInutNFCom')->item(0);
        if (!isset($retInutNFCom)) {
            throw DocumentsException::wrongDocument(3, "&lt;retInutNFCom;");
        }
        $retversao = $retInutNFCom->getAttribute("versao");
        $retInfInut = $ret->getElementsByTagName('infInut')->item(0);
        $cStat = $retInfInut->getElementsByTagName('cStat')->item(0)->nodeValue;
        $xMotivo = $retInfInut->getElementsByTagName('xMotivo')->item(0)->nodeValue;
        if ($cStat != 102) {
            throw DocumentsException::wrongDocument(4, "[$cStat] $xMotivo.");
        }
        $rettpAmb = $retInfInut->getElementsByTagName('tpAmb')->item(0)->nodeValue;
        $retcUF = !empty($retInfInut->getElementsByTagName('cUF')->item(0)->nodeValue)
            ? $retInfInut->getElementsByTagName('cUF')->item(0)->nodeValue : $cUF;
        $retano = $retInfInut->getElementsByTagName('ano')->item(0)->nodeValue;
        $retcpfCnpj = $retInfInut->getElementsByTagName($cpfOrCnpjTag)->item(0)->nodeValue;
        $retmod = $retInfInut->getElementsByTagName('mod')->item(0)->nodeValue;
        $retserie = $retInfInut->getElementsByTagName('serie')->item(0)->nodeValue;
        $retnNFIni = $retInfInut->getElementsByTagName('nNFIni')->item(0)->nodeValue;
        $retnNFFin = $retInfInut->getElementsByTagName('nNFFin')->item(0)->nodeValue;
        if (
            $versao != $retversao ||
            $tpAmb != $rettpAmb ||
            $cUF != $retcUF ||
            $ano != $retano ||
            $cpfOrCnpjTagValue != $retcpfCnpj ||
            $mod != $retmod ||
            $serie != $retserie ||
            $nNFIni != $retnNFIni ||
            $nNFFin != $retnNFFin
        ) {
            throw DocumentsException::wrongDocument(5);
        }
        return self::join(
            $req->saveXML($inutNFCom),
            $ret->saveXML($retInutNFCom),
            'ProcInutNFCom',
            $versao
        );
    }

    /**
     * Authorize NFCom
     * @throws \InvalidArgumentException
     */
    protected static function addNFComProtocol(string $request, string $response): string
    {
        $req = new DOMDocument('1.0', 'UTF-8');
        $req->preserveWhiteSpace = false;
        $req->formatOutput = false;
        $req->loadXML($request);

        $nfcom = $req->getElementsByTagName('NFCom')->item(0);
        $infNFCom = $req->getElementsByTagName('infNFCom')->item(0);
        $versao = $infNFCom->getAttribute("versao");
        $chave = preg_replace('/[^0-9]/', '', $infNFCom->getAttribute("Id"));
        $digNFCom = $req->getElementsByTagName('DigestValue')
            ->item(0)
            ->nodeValue;

        $ret = new DOMDocument('1.0', 'UTF-8');
        $ret->preserveWhiteSpace = false;
        $ret->formatOutput = false;
        $ret->loadXML($response);
        $retProt = $ret->getElementsByTagName('protNFCom')->length > 0 ? $ret->getElementsByTagName('protNFCom') : null;
        if ($retProt === null) {
            throw DocumentsException::wrongDocument(3, "&lt;protNFCom&gt;");
        }
        $digProt = null;
        foreach ($retProt as $rp) {
            $infProt = $rp->getElementsByTagName('infProt')->item(0);
            $cStat = $infProt->getElementsByTagName('cStat')->item(0)->nodeValue;
            $xMotivo = $infProt->getElementsByTagName('xMotivo')->item(0)->nodeValue;
            $dig = $infProt->getElementsByTagName("digVal")->item(0);
            $key = $infProt->getElementsByTagName("chNFCom")->item(0)->nodeValue;
            if (isset($dig)) {
                $digProt = $dig->nodeValue;
                if ($digProt == $digNFCom && $chave == $key) {
                    //100 Autorizado
                    //150 Autorizado fora do prazo
                    //110 Uso Denegado
                    //205 NFCom Denegada
                    //301 Uso denegado por irregularidade fiscal do emitente
                    //302 Uso denegado por irregularidade fiscal do destinatário
                    //303 Uso Denegado Destinatario nao habilitado a operar na UF
                    $cstatpermit = ['100', '150', '110', '205', '301', '302', '303'];
                    if (!in_array($cStat, $cstatpermit)) {
                        throw DocumentsException::wrongDocument(4, "[$cStat] $xMotivo");
                    }
                    return self::join(
                        $req->saveXML($nfcom),
                        $ret->saveXML($rp),
                        'nfcomProc',
                        $versao
                    );
                }
            }
        }
        if (empty($digProt)) {
            $prot = $ret->getElementsByTagName('protNFCom')->item(0);
            $cStat = $prot->getElementsByTagName('cStat')->item(0)->nodeValue;
            $xMotivo = $prot->getElementsByTagName('xMotivo')->item(0)->nodeValue;
            throw DocumentsException::wrongDocument(18, "[{$cStat}] {$xMotivo}");
        }
        if ($digNFCom !== $digProt) {
            throw DocumentsException::wrongDocument(5, "Os digest são diferentes [{$chave}]");
        }
        return $req->saveXML();
    }

    /**
     * Authorize Event
     * @throws \InvalidArgumentException
     */
    protected static function addEnvEventoProtocol(string $request, string $response): string
    {
        $ev = new \DOMDocument('1.0', 'UTF-8');
        $ev->preserveWhiteSpace = false;
        $ev->formatOutput = false;
        $ev->loadXML($request);
        //extrai numero do lote do envio
        $envLote = $ev->getElementsByTagName('idLote')->item(0)->nodeValue;
        //extrai tag evento do xml origem (solicitação)
        $event = $ev->getElementsByTagName('evento')->item(0);
        $versao = $event->getAttribute('versao');

        $ret = new \DOMDocument('1.0', 'UTF-8');
        $ret->preserveWhiteSpace = false;
        $ret->formatOutput = false;
        $ret->loadXML($response);
        //extrai numero do lote da resposta
        $resLote = $ret->getElementsByTagName('idLote')->item(0)->nodeValue;
        //extrai a rag retEvento da resposta (retorno da SEFAZ)
        $retEv = $ret->getElementsByTagName('retEvento')->item(0);
        $cStat  = $retEv->getElementsByTagName('cStat')->item(0)->nodeValue;
        $xMotivo = $retEv->getElementsByTagName('xMotivo')->item(0)->nodeValue;
        $tpEvento = $retEv->getElementsByTagName('tpEvento')->item(0)->nodeValue;
        $cStatValids = ['135', '136'];
        if ($tpEvento == Tools::EVT_CANCELA) {
            $cStatValids[] = '155';
        }
        if (!in_array($cStat, $cStatValids)) {
            throw DocumentsException::wrongDocument(4, "[$cStat] $xMotivo");
        }
        if ($resLote !== $envLote) {
            throw DocumentsException::wrongDocument(
                5,
                "Os numeros de lote dos documentos são diferentes."
            );
        }
        return self::join(
            $ev->saveXML($event),
            $ret->saveXML($retEv),
            'procEventoNFCom',
            $versao
        );
    }

    /**
     * Join the pieces of the source document with those of the answer
     */
    protected static function join(string $first, string $second, string $nodename, string $versao): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
                . "<$nodename versao=\"$versao\" "
                . "xmlns=\"" . self::$urlPortal . "\">";
        $xml .= $first;
        $xml .= $second;
        $xml .= "</$nodename>";
        return $xml;
    }
}
