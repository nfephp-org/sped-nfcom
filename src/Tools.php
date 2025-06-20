<?php

/**
 * Class responsible for communication with SEFAZ extends
 * NFePHP\NFCom\Common\Tools
 *
 * @category  NFePHP
 * @package   NFePHP\NFCom\Tools
 * @copyright NFePHP Copyright (c) 2008-2020
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-nfcom for the canonical source repository
 */

namespace NFePHP\NFCom;

use NFePHP\Common\Strings;
use NFePHP\Common\Signer;
use NFePHP\Common\UFList;
use NFePHP\NFCom\Common\Tools as ToolsCommon;
use InvalidArgumentException;

class Tools extends ToolsCommon
{
    public const EVT_CANCELA = 110111; //only seq=1
    public const EVT_CANCELASUBSTITUICAO = 110112;
    public const EVT_EPEC = 110140; //only seq=1
    public const EVT_CONCILIACAO = 110750;
    public const EVT_CANCELA_CONCILIACAO = 110751;

    /**
     *
     * @param string $xml XML assinado da NFCom (modelo 62)
     * @return string XML de resposta da SEFAZ
     * @throws InvalidArgumentException
     */
    public function sefazEnvia(
        string $Xml
    ): string {
        $servico = 'NFComRecepcao';
        $this->checkContingencyForWebServices($servico);
        if ($this->contingency->type != '') {
            // Em modo de contingencia esses XMLs deverão ser modificados e re-assinados e retornados
            // no parametro $xmls para serem armazenados pelo aplicativo pois serão alterados.
            $this->correctNFComForContingencyMode($Xml);
        }

        $this->checkModelFromXml($Xml);
        $request = trim(preg_replace("/<\?xml.*?\?>/", "", $Xml));
        $this->servico($servico, $this->config->siglaUF, $this->tpAmb);
        $this->isValid($this->urlVersion, $request, 'nfcom');

        $this->lastRequest = $request;
        //montagem dos dados da mensagem SOAP
        $gzdata = base64_encode(gzencode($request, 9, FORCE_GZIP));
        $parameters = ['nfcomDadosMsg' => $gzdata];
        $body = "<nfcomDadosMsg xmlns=\"$this->urlNamespace\">$gzdata</nfcomDadosMsg>";
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }

    /**
     * Search for the registration data of an NFCom issuer,
     * if in contingency mode this service will cause a
     * Exception and remember not all Sefaz have this service available,
     * so it will not work in some cases.
     * @param string $uf federation unit (abbreviation)
     * @param string $cnpj CNPJ number (optional)
     * @param string $iest IE number (optional)
     * @param string $cpf CPF number (optional)
     * @return string xml soap response
     * @throws InvalidArgumentException
     */
    public function sefazCadastro(string $uf, string $cnpj = '', string $iest = '', string $cpf = ''): string
    {
        $filter = '';
        if (!empty($cnpj)) {
            $filter = "<CNPJ>$cnpj</CNPJ>";
        } elseif (!empty($iest)) {
            $filter = "<IE>$iest</IE>";
        } elseif (!empty($cpf)) {
            $filter = "<CPF>$cpf</CPF>";
        }
        if (empty($uf) || empty($filter)) {
            throw new InvalidArgumentException('Sigla UF esta vazia ou CNPJ+IE+CPF vazios!');
        }
        //carrega serviço
        $servico = 'NfeConsultaCadastro';
        $this->checkContingencyForWebServices($servico);
        $this->servico($servico, $uf, $this->tpAmb, true);
        $request = "<ConsCad xmlns=\"$this->urlPortal\" versao=\"$this->urlVersion\">"
            . "<infCons>"
            . "<xServ>CONS-CAD</xServ>"
            . "<UF>$uf</UF>"
            . "$filter"
            . "</infCons>"
            . "</ConsCad>";

        $this->isValid($this->urlVersion, $request, 'consCad');
        if (strtoupper($uf) === 'MT') {
            $request = "<nfeDadosMsg>$request</nfeDadosMsg>" ;
        }
        $this->lastRequest = $request;
        $parameters = ['nfeDadosMsg' => $request];
        if ($this->urlVersion === '2.00') {
            $this->objHeader = new \SoapHeader(
                $this->urlNamespace,
                'nfeCabecMsg',
                ['cUF' => $this->urlcUF, 'versaoDados' => $this->urlVersion]
            );
        }
        $body = "<nfeDadosMsg xmlns=\"$this->urlNamespace\">$request</nfeDadosMsg>";
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }

    /**
     * Check services status SEFAZ/SVC
     * If $uf is empty use normal check with contingency
     * If $uf is NOT empty ignore contingency mode
     * @param string $uf  initials of federation unit
     * @param int $tpAmb
     * @param bool $ignoreContingency
     * @return string xml soap response
     */
    public function sefazStatus(string $uf = '', ?int $tpAmb = null, bool $ignoreContingency = true): string
    {
        if (empty($tpAmb)) {
            $tpAmb = $this->tpAmb;
        }
        if (empty($uf)) {
            $uf = $this->config->siglaUF;
            $ignoreContingency = false;
        }
        $servico = 'NFComStatusServico';
        $this->checkContingencyForWebServices($servico);
        $this->servico($servico, $uf, $tpAmb, $ignoreContingency);
        $request = "<consStatServNFCom xmlns=\"$this->urlPortal\" versao=\"$this->urlVersion\">"
            . "<tpAmb>$tpAmb</tpAmb>"
            . "<cUF>$this->urlcUF</cUF>"
            . "<xServ>STATUS</xServ>"
            . "</consStatServNFCom>";
        $this->isValid($this->urlVersion, $request, 'consStatServNFCom');
        $this->lastRequest = $request;
        $parameters = ['nfcomDadosMsg' => $request];
        $body = "<nfcomDadosMsg xmlns=\"$this->urlNamespace\">$request</nfcomDadosMsg>";
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }

    /**
     * Requires nfcom cancellation
     * @param  string $chave key of NFCom
     * @param  string $xJust justificative 255 characters max
     * @param  string $nProt protocol number
     * @return string
     * @throws InvalidArgumentException
     */
    public function sefazCancela(
        string $chave,
        string $xJust,
        string $nProt,
        ?\DateTimeInterface $dhEvento = null,
        ?string $lote = null
    ): string {
        if (empty($chave) || empty($xJust) || empty($nProt)) {
            throw new InvalidArgumentException('Cancelamento: chave, just ou numprot vazio!');
        }
        $uf = $this->validKeyByUF($chave);
        $xJust = Strings::replaceUnacceptableCharacters(substr(trim($xJust), 0, 255));
        $nSeqEvento = 1;
        $tagAdic = "<nProt>$nProt</nProt><xJust>$xJust</xJust>";
        return $this->sefazEvento($uf, $chave, self::EVT_CANCELA, $nSeqEvento, $tagAdic, $dhEvento, $lote);
    }

    /**
     * Check the NFCom status for the 44-digit key and retrieve the protocol
     * @param string $chave
     * @param int $tpAmb
     * @throws InvalidArgumentException
     */
    public function sefazConsultaChave(string $chave, ?int $tpAmb = null): string
    {
        if (empty($chave)) {
            throw new InvalidArgumentException('Consulta chave: a chave esta vazia!');
        }
        if (strlen($chave) != 44 || !is_numeric($chave)) {
            throw new InvalidArgumentException("Consulta chave: chave \"$chave\" invalida!");
        }
        $uf = UFList::getUFByCode((int)substr($chave, 0, 2));
        if (empty($tpAmb)) {
            $tpAmb = $this->tpAmb;
        }
        //carrega serviço
        $servico = 'NFComConsulta';
        $this->checkContingencyForWebServices($servico);
        $this->servico($servico, $uf, $tpAmb);
        $request = "<consSitNFCom xmlns=\"$this->urlPortal\" versao=\"$this->urlVersion\">"
            . "<tpAmb>$tpAmb</tpAmb>"
            . "<xServ>CONSULTAR</xServ>"
            . "<chNFCom>$chave</chNFCom>"
            . "</consSitNFCom>";
        $this->isValid($this->urlVersion, $request, 'consSitNFCom');
        $this->lastRequest = $request;
        $parameters = ['nfcomDadosMsg' => $request];
        $body = "<nfcomDadosMsg xmlns=\"$this->urlNamespace\">$request</nfcomDadosMsg>";
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }


    /**
     * Send event to SEFAZ
     * @param string $uf
     * @param string $chave
     * @param int $tpEvento
     * @param int $nSeqEvento
     * @param string $tagAdic
     * @return string
     * @throws \Exception
     */
    public function sefazEvento(
        string $uf,
        string $chave,
        int $tpEvento,
        int $nSeqEvento = 1,
        string $tagAdic = '',
        ?\DateTimeInterface $dhEvento = null,
        ?string $lote = null
    ): string {
        $eventos = [
            self::EVT_CANCELA => ['versao' => '1.00', 'nome' => 'evCancNFCom']
        ];
        $verEvento = $this->urlVersion;
        if (!empty($eventos[$tpEvento])) {
            $evt = $eventos[$tpEvento];
            $verEvento = $evt['versao'];
        }
        $ignore = $tpEvento == self::EVT_EPEC;
        $servico = 'NFComRecepcaoEvento';
        $this->checkContingencyForWebServices($servico);
        $this->servico($servico, $uf, $this->tpAmb, $ignore);
        $ev = $this->tpEv($tpEvento);
        $descEvento = $ev->desc;
        $cnpj = $this->config->cnpj ?? '';
        $dt = new \DateTime(date("Y-m-d H:i:sP"), new \DateTimeZone($this->timezone));
        $dt->setTimezone(new \DateTimeZone($this->timezone));
        $dhEventoString = $dt->format('Y-m-d\TH:i:sP');
        if ($dhEvento != null) {
            $dhEventoString = $dhEvento->format('Y-m-d\TH:i:sP');
        }
        $sSeqEvento = str_pad((string)$nSeqEvento, 2, "0", STR_PAD_LEFT);
        $eventId = "ID" . $tpEvento . $chave . $sSeqEvento;
        //NT 2024.002 versão 1.00 - Maio 2024, comentário P08 elemento cOrgao
        if (in_array($tpEvento, [self::EVT_CONCILIACAO, self::EVT_CANCELA_CONCILIACAO]) && $uf === 'SVRS') {
            $cOrgao = 92;
        } else {
            $cOrgao = UFList::getCodeByUF($uf);
        }
        $request = "<eventoNFCom xmlns=\"$this->urlPortal\" versao=\"$this->urlVersion\">"
            . "<infEvento Id=\"$eventId\">"
            . "<cOrgao>$cOrgao</cOrgao>"
            . "<tpAmb>$this->tpAmb</tpAmb>";
        if ($this->typePerson === 'J') {
            $request .= "<CNPJ>$cnpj</CNPJ>";
        } else {
            $request .= "<CPF>$cnpj</CPF>";
        }
        $request .= "<chNFCom>$chave</chNFCom>"
            . "<dhEvento>$dhEventoString</dhEvento>"
            . "<tpEvento>$tpEvento</tpEvento>"
            . "<nSeqEvento>$nSeqEvento</nSeqEvento>"
            . "<verEvento>$verEvento</verEvento>"
            . "<detEvento versao=\"$verEvento\">"
            . "<descEvento>$descEvento</descEvento>"
            . "$tagAdic"
            . "</detEvento>"
            . "</infEvento>"
            . "</eventoNFCom>";
        //assinatura dos dados
        $request = Signer::sign(
            $this->certificate,
            $request,
            'infEvento',
            'Id',
            $this->algorithm,
            $this->canonical
        );
        $request = Strings::clearXmlString($request, true);
        if ($lote == null) {
            $lote = $dt->format('YmdHis') . random_int(0, 9);
        }
        $request = "<eventoNFCom xmlns=\"$this->urlPortal\" versao=\"$this->urlVersion\">"
            . "<idLote>$lote</idLote>"
            . $request
            . "</eventoNFCom>";
        if (!empty($eventos[$tpEvento])) {
            $evt = $eventos[$tpEvento];
            $this->isValid($evt['versao'], $request, $evt['nome']);
        } else {
            $this->isValid($this->urlVersion, $request, 'eventoNFCom');
        }
        $this->lastRequest = $request;
        $parameters = ['nfcomDadosMsg' => $request];
        $body = "<nfcomDadosMsg xmlns=\"$this->urlNamespace\">$request</nfcomDadosMsg>";
        $this->lastResponse = $this->sendRequest($body, $parameters);
        return $this->lastResponse;
    }
}
