<?php

namespace NFePHP\NFCom;

use DateTime;
use NFePHP\Common\Keys;
use NFePHP\Common\DOMImproved as Dom;
use NFePHP\Common\Strings;
use RuntimeException;
use InvalidArgumentException;
use stdClass;

class Make
{
    public $errors = [];
    public $chNFCom;
    public $xml;
    public $dom;
    protected $NFCom;

    protected $replaceAccentedChars = false;

    protected $infNFCom;
    protected $ide;
    protected $emit;
    protected $enderEmit;
    protected $dest;
    protected $enderDest;
    protected $assinante;
    protected $gSub;
    protected $gNF;
    protected $gCofat;
    protected $gDif;
    protected $gDevTrib;
    protected $gRed;
    protected $aDet = [];
    protected $aProd = [];
    protected $aICMS = [];
    protected $aICMSSN = [];
    protected $aICMSUFDest = [];
    protected $aPIS = [];
    protected $aCOFINS = [];
    protected $aFUST = [];
    protected $aFUNTTEL = [];
    protected $aRetTrib = [];
    protected $aIBSCBS = [];
    protected $aGIBSCBS = [];
    protected $aGIBSUF = [];
    protected $aGIBSMun = [];
    protected $aGCBS = [];
    protected $aGTribRegular = [];
    protected $aGIBSCredPres = [];
    protected $aGCBSCredPres = [];
    protected $aGTribCompraGov = [];
    protected $aGProc = [];
    protected $aGRessarc = [];
    protected $total;
    protected $gFidelidade;
    protected $gFat;
    protected $gFatCentral;
    protected $aAutXML = [];
    protected $infAdic;
    protected $gRespTec;
    protected $infNFComSupl;

    public function __construct()
    {
        $this->dom = new Dom('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = false;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagInfNFCom(stdClass $std)
    {
        $possible = ['Id', 'versao'];
        $std = $this->equilizeParameters($std, $possible);
        $chave = preg_replace('/[^0-9]/', '', $std->Id);
        $this->infNFCom = $this->dom->createElement("infNFCom");
        $this->infNFCom->setAttribute("Id", 'NFCom' . $chave);
        $this->infNFCom->setAttribute(
            "versao",
            $std->versao
        );
        $this->chNFCom = $chave;
        return $this->infNFCom;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagIde(stdClass $std)
    {
        $possible = [
            'cUF',
            'tpAmb',
            'mod',
            'serie',
            'nNF',
            'cNF',
            'cDV',
            'dhEmi',
            'tpEmis',
            'nSiteAutoriz',
            'cMunFG',
            'finNFCom',
            'tpFat',
            'verProc',
            'indPrePago',
            'indCessaoMeiosRede',
            'dhCont',
            'xJust',
        ];
        $std = $this->equilizeParameters($std, $possible);

        if (empty($std->cNF)) {
            $std->cNF = Keys::random($std->nNF);
        }
        if (empty($std->cDV)) {
            $std->cDV = 0;
        }
         $std->cNF = str_pad($std->cNF, 7, '0', STR_PAD_LEFT);
        if (intval($std->cNF) == intval($std->nNF)) {
            throw new InvalidArgumentException("O valor [{$std->cNF}] não é " . " aceitável para cNF,
              não pode ser igual ao de nNF, vide NT2019.001");
        }
//         if (method_exists(Keys::class, 'cNFIsValid')) {
//             if (!Keys::cNFIsValid($std->cNF)) {
//                 throw new InvalidArgumentException("O valor [{$std->cNF}] para cNF " . " é invalido,
//                  deve respeitar a NT2019.001");
//             }
//         }

        $identificador = '<ide> - ';

        $ide = $this->dom->createElement("ide");
        $this->dom->addChild(
            $ide,
            "cUF",
            $std->cUF,
            true,
            $identificador . "Código da UF do emitente do Documento Fiscal"
        );
        $this->dom->addChild(
            $ide,
            "tpAmb",
            $std->tpAmb,
            true,
            $identificador . "Identificação do Ambiente"
        );
        $this->dom->addChild(
            $ide,
            "mod",
            $std->mod,
            true,
            $identificador . "Código do Modelo do Documento Fiscal"
        );
        $this->dom->addChild(
            $ide,
            "serie",
            $std->serie,
            true,
            $identificador . "Série do Documento Fiscal"
        );
        $this->dom->addChild(
            $ide,
            "nNF",
            $std->nNF,
            true,
            $identificador . "Número do Documento Fiscal"
        );
        $this->dom->addChild(
            $ide,
            "cNF",
            $std->cNF,
            true,
            $identificador . "Código Numérico que compõe a Chave de Acesso"
        );
        $this->dom->addChild(
            $ide,
            "cDV",
            !empty($std->cDV) ? $std->cDV : '0',
            true,
            $identificador . "Dígito Verificador da Chave de Acesso"
        );
        $this->dom->addChild(
            $ide,
            "dhEmi",
            $std->dhEmi,
            true,
            $identificador . "Data e hora de emissão do Documento Fiscal"
        );
        $this->dom->addChild(
            $ide,
            "tpEmis",
            $std->tpEmis,
            true,
            $identificador . "Tipo de Emissão da Documento Fiscal"
        );
        $this->dom->addChild(
            $ide,
            "nSiteAutoriz",
            $std->nSiteAutoriz,
            true,
            $identificador . "Identificação do número do Site do Autorizador de recepção da NFCom"
        );
        $this->dom->addChild(
            $ide,
            "cMunFG",
            $std->cMunFG,
            true,
            $identificador . "Código do Município de Ocorrência do Fato Gerador"
        );
        $this->dom->addChild(
            $ide,
            "finNFCom",
            $std->finNFCom,
            true,
            $identificador . "Finalidade de emissão da NFCom"
        );
        $this->dom->addChild(
            $ide,
            "tpFat",
            $std->tpFat,
            true,
            $identificador . "Tipo de Faturamento da NFCom"
        );
        $this->dom->addChild(
            $ide,
            "verProc",
            $std->verProc,
            true,
            $identificador . "Versão do Processo de emissão"
        );
        $this->dom->addChild(
            $ide,
            "indPrePago",
            $std->indPrePago,
            false,
            $identificador . "Indicador de serviço pré-pago"
        );
        $this->dom->addChild(
            $ide,
            "indCessaoMeiosRede",
            $std->indCessaoMeiosRede,
            false,
            $identificador . "Indicador de Sessão de Meios de Rede"
        );
        if (!empty($std->dhCont) && !empty($std->xJust)) {
            $this->dom->addChild(
                $ide,
                "dhCont",
                $std->dhCont,
                true,
                $identificador . "Data e Hora da entrada em contingência"
            );
            $this->dom->addChild(
                $ide,
                "xJust",
                substr(trim($std->xJust), 0, 256),
                true,
                $identificador . "Justificativa da entrada em contingência"
            );
        }

        $this->ide = $ide;
        return $ide;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagEmit(stdClass $std)
    {
        $possible = [
            'CNPJ',
            'IE',
            'IEUFDest',
            'CRT',
            'xNome',
            'xFant',
        ];
        $std = $this->equilizeParameters($std, $possible);
        $identificador = '<emit> - ';

        $this->emit = $this->dom->createElement("emit");
        $this->dom->addChild(
            $this->emit,
            "CNPJ",
            Strings::onlyNumbers($std->CNPJ),
            false,
            $identificador . "CNPJ do emitente"
        );
        if ($std->IE != 'ISENTO') {
            $std->IE = Strings::onlyNumbers($std->IE);
        }
        $this->dom->addChild(
            $this->emit,
            "IE",
            $std->IE,
            true,
            $identificador . "Inscrição Estadual do emitente"
        );
        $this->dom->addChild(
            $this->emit,
            "IEUFDest",
            Strings::onlyNumbers($std->IEUFDest),
            false,
            $identificador . "Inscrição Estadual Virtual do emitente na UF de Destino da partilha (IE Virtual)"
        );
        $this->dom->addChild(
            $this->emit,
            "CRT",
            $std->CRT,
            true,
            $identificador . "Código de Regime Tributário do emitente"
        );
        $this->dom->addChild(
            $this->emit,
            "xNome",
            substr(trim($std->xNome), 0, 60),
            true,
            $identificador . "Razão Social ou Nome do emitente"
        );
        if (!empty($std->xFant)) {
            $this->dom->addChild(
                $this->emit,
                "xFant",
                substr(trim($std->xFant), 0, 60),
                false,
                $identificador . "Nome fantasia do emitente"
            );
        }
        return $this->emit;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagEnderEmit(stdClass $std)
    {
        $possible = [
            'xLgr',
            'nro',
            'xCpl',
            'xBairro',
            'cMun',
            'xMun',
            'CEP',
            'UF',
            'fone',
            'email'
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<enderEmit> - ';
        $this->enderEmit = $this->dom->createElement("enderEmit");
        $this->dom->addChild(
            $this->enderEmit,
            "xLgr",
            substr(trim($std->xLgr), 0, 60),
            true,
            $identificador . "Logradouro do Endereço do emitente"
        );
        $this->dom->addChild(
            $this->enderEmit,
            "nro",
            substr(trim($std->nro), 0, 60),
            true,
            $identificador . "Número do Endereço do emitente"
        );
        $this->dom->addChild(
            $this->enderEmit,
            "xCpl",
            substr(trim($std->xCpl), 0, 60),
            false,
            $identificador . "Complemento do Endereço do emitente"
        );
        $this->dom->addChild(
            $this->enderEmit,
            "xBairro",
            substr(trim($std->xBairro), 0, 60),
            true,
            $identificador . "Bairro do Endereço do emitente"
        );
        $this->dom->addChild(
            $this->enderEmit,
            "cMun",
            Strings::onlyNumbers($std->cMun),
            true,
            $identificador . "Código do município do Endereço do emitente"
        );
        $this->dom->addChild(
            $this->enderEmit,
            "xMun",
            substr(trim($std->xMun), 0, 60),
            true,
            $identificador . "Nome do município do Endereço do emitente"
        );
        $this->dom->addChild(
            $this->enderEmit,
            "CEP",
            Strings::onlyNumbers($std->CEP),
            true,
            $identificador . "Código do CEP do Endereço do emitente"
        );
        $this->dom->addChild(
            $this->enderEmit,
            "UF",
            strtoupper(trim($std->UF)),
            true,
            $identificador . "Sigla da UF do Endereço do emitente"
        );
        if (!empty($std->fone)) {
            $this->dom->addChild(
                $this->enderEmit,
                "fone",
                trim($std->fone),
                false,
                $identificador . "Telefone do Endereço do emitente"
            );
        }
        if (!empty($std->email)) {
            $this->dom->addChild(
                $this->enderEmit,
                "email",
                trim($std->email),
                false,
                $identificador . "Endereço de E-mail do emitente"
            );
        }
        $this->emit->appendChild($this->enderEmit);
        return $this->enderEmit;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagDest(stdClass $std)
    {
        $possible = [
            'xNome',
            'CNPJ',
            'CPF',
            'idOutros',
            'indIEDest',
            'IE',
            'IM',
        ];
        $std = $this->equilizeParameters($std, $possible);
        $identificador = '<dest> - ';

        if ($std->indIEDest === 2) {
            $std->IE = "ISENTO";
        }

        $this->dest = $this->dom->createElement("dest");
        $this->dom->addChild(
            $this->dest,
            "xNome",
            substr(trim($std->xNome), 0, 60),
            true,
            $identificador . "Razão Social ou Nome do destinatário"
        );
        if (!empty($std->CNPJ)) {
            $this->dom->addChild(
                $this->dest,
                "CNPJ",
                Strings::onlyNumbers($std->CNPJ),
                true,
                $identificador . "CNPJ do destinatário"
            );
        } elseif (!empty($std->CPF)) {
            $this->dom->addChild(
                $this->dest,
                "CPF",
                Strings::onlyNumbers($std->CPF),
                true,
                $identificador . "CPF do destinatário"
            );
        } elseif ($std->idOutros !== null) {
            $this->dom->addChild(
                $this->dest,
                "idOutros",
                $std->idOutros,
                true,
                $identificador . "Identificação do destinatário no caso de comprador estrangeiro"
            );
        }
        $this->dom->addChild(
            $this->dest,
            "indIEDest",
            Strings::onlyNumbers($std->indIEDest),
            true,
            $identificador . "Indicador da IE do Destinatário"
        );
        if (!empty($std->IE)) {
            $this->dom->addChild(
                $this->dest,
                "IE",
                $std->IE,
                false,
                $identificador . "Inscrição Estadual do Destinatário"
            );
        }
        if (!empty($std->IM)) {
            $this->dom->addChild(
                $this->dest,
                "IM",
                Strings::onlyNumbers($std->IM),
                false,
                $identificador . "Inscrição Municipal do destinatário"
            );
        }
        return $this->dest;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagEnderDest(stdClass $std)
    {
        $possible = [
            'xLgr',
            'nro',
            'xCpl',
            'xBairro',
            'cMun',
            'xMun',
            'CEP',
            'UF',
            'fone',
            'email',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<enderDest> - ';
        if (empty($this->dest)) {
            throw new RuntimeException('A TAG dest deve ser criada antes do endereço do mesmo.');
        }
        $this->enderDest = $this->dom->createElement("enderDest");
        $this->dom->addChild(
            $this->enderDest,
            "xLgr",
            $std->xLgr,
            true,
            $identificador . "Logradouro do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "nro",
            $std->nro,
            true,
            $identificador . "Número do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "xCpl",
            $std->xCpl,
            false,
            $identificador . "Complemento do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "xBairro",
            $std->xBairro,
            true,
            $identificador . "Bairro do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "cMun",
            $std->cMun,
            true,
            $identificador . "Código do município do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "xMun",
            $std->xMun,
            true,
            $identificador . "Nome do município do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "CEP",
            $std->CEP,
            false,
            $identificador . "Código do CEP do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "UF",
            $std->UF,
            true,
            $identificador . "Sigla da UF do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "cPais",
            $std->cPais,
            false,
            $identificador . "Código do País do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "xPais",
            $std->xPais,
            false,
            $identificador . "Nome do País do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "fone",
            $std->fone,
            false,
            $identificador . "Telefone do Endereço do Destinatário"
        );
        $this->dom->addChild(
            $this->enderDest,
            "email",
            $std->email,
            false,
            $identificador . "Email do Endereço do Destinatário"
        );
        $this->dest->appendChild($this->enderDest);
        return $this->enderDest;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagAssinante(stdClass $std)
    {
        $possible = [
            'iCodAssinante',
            'tpAssinante',
            'tpServUtil',
            'nContrato',
            'dContratoIni',
            'dContratoFim',
            'NroTermPrinc',
            'cUFPrinc',
            'NroTermAdic',
            'cUF',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<assinante> - ';
        $this->assinante = $this->dom->createElement("assinante");
        $this->dom->addChild(
            $this->assinante,
            "iCodAssinante",
            $std->iCodAssinante,
            true,
            $identificador . "Código único de Identificação do assinante"
        );
        $this->dom->addChild(
            $this->assinante,
            "tpAssinante",
            $std->tpAssinante,
            true,
            $identificador . "Tipo de assinante"
        );
        $this->dom->addChild(
            $this->assinante,
            "tpServUtil",
            $std->tpServUtil,
            true,
            $identificador . "Tipo de serviço utilizado do assinante"
        );
        $this->dom->addChild(
            $this->assinante,
            "nContrato",
            $std->nContrato,
            false,
            $identificador . "Número do Contrato do assinante"
        );
        $this->dom->addChild(
            $this->assinante,
            "dContratoIni",
            $std->dContratoIni,
            false,
            $identificador . "Data de início do contrato do assinante"
        );
        $this->dom->addChild(
            $this->assinante,
            "dContratoFim",
            $std->dContratoFim,
            false,
            $identificador . "Data de término do contrato do assinante"
        );
        if (!empty($std->NroTermPrinc) && !empty($std->cUFPrinc)) {
            $this->dom->addChild(
                $this->assinante,
                "NroTermPrinc",
                $std->NroTermPrinc,
                true,
                $identificador . "Número do Terminal Principal do serviço contratado do assinante"
            );
            $this->dom->addChild(
                $this->assinante,
                "cUFPrinc",
                $std->cUFPrinc,
                true,
                $identificador . "Código da UF de habilitação do terminal do assinante"
            );
        }
        if (!empty($std->NroTermAdic) && !empty($std->cUFAdic)) {
            $this->dom->addChild(
                $this->assinante,
                "NroTermAdic",
                $std->NroTermAdic,
                true,
                $identificador . "Número dos Terminais adicionais do serviço contratado do assinante"
            );
            $this->dom->addChild(
                $this->assinante,
                "cUFAdic",
                $std->cUFAdic,
                true,
                $identificador . "Código da UF de habilitação do terminal do assinante"
            );
        }
        return $this->assinante;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGSub(stdClass $std)
    {
        $possible = [
            'chNFCom',
            'motSub',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gSub> - ';
        $this->gSub = $this->dom->createElement("gSub");
        $this->dom->addChild(
            $this->gSub,
            "chNFCom",
            $std->chNFCom,
            true,
            $identificador . "Chave de acesso da NFCom original"
        );
        $this->dom->addChild(
            $this->gSub,
            "motSub",
            $std->motSub,
            true,
            $identificador . "Motivo da substituição"
        );
        return $this->gSub;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGNF(stdClass $std)
    {
        $possible = [
            'CNPJ',
            'mod',
            'serie',
            'nNF',
            'CompetEmis',
            'hash115',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gNF> - ';
        if (empty($this->dest)) {
            throw new RuntimeException('A TAG dest deve ser criada antes do endereço do mesmo.');
        }
        $this->gNF = $this->dom->createElement("gNF");
        $this->dom->addChild(
            $this->gNF,
            "CNPJ",
            $std->CNPJ,
            true,
            $identificador . "CNPJ do Emitente "
        );
        $this->dom->addChild(
            $this->gNF,
            "mod",
            $std->mod,
            true,
            $identificador . "Modelo do documento"
        );
        $this->dom->addChild(
            $this->gNF,
            "serie",
            $std->serie,
            false,
            $identificador . "Serie do documento fiscal"
        );
        $this->dom->addChild(
            $this->gNF,
            "nNF",
            $std->nNF,
            true,
            $identificador . "Número do documento fiscal"
        );
        $this->dom->addChild(
            $this->gNF,
            "CompetEmis",
            str_replace('-', '', $std->CompetEmis),
            true,
            $identificador . "Ano e mês da emissão da NF"
        );
        if ($std->hash115) {
            $this->dom->addChild(
                $this->gNF,
                "hash115",
                $std->hash115,
                false,
                $identificador . "Hash do registro no arquivo do convênio 115"
            );
        }

        $node = $this->dest->getElementsByTagName("motSub")->item(0);
        $this->gSub->insertBefore($this->gNF, $node);
        return $this->gNF;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGCofat(stdClass $std)
    {
        $possible = [
            'chNFComLocal',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gCofat> - ';
        $this->gCofat = $this->dom->createElement("gCofat");
        $this->dom->addChild(
            $this->gCofat,
            "chNFComLocal",
            $std->chNFComLocal,
            true,
            $identificador . "Chave de acesso da NFCom emitida pela Operadora Local"
        );
        return $this->gCofat;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGNFCofat(stdClass $std)
    {
        $possible = [
            'CNPJ',
            'mod',
            'serie',
            'nNF',
            'CompetEmis',
            'hash115',
            'motSub',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gNF> - ';
        if (empty($this->dest)) {
            throw new RuntimeException('A TAG dest deve ser criada antes do endereço do mesmo.');
        }
        $this->gNF = $this->dom->createElement("gNF");
        $this->dom->addChild($this->gNF, "CNPJ", $std->CNPJ, true, $identificador . "CNPJ do Emitente ");
        $this->dom->addChild($this->gNF, "mod", $std->mod, true, $identificador . "Modelo do documento");
        $this->dom->addChild($this->gNF, "serie", $std->serie, false, $identificador . "Serie do documento fisca");
        $this->dom->addChild($this->gNF, "nNF", $std->nNF, true, $identificador . "Número do documento fiscal");
        $this->dom->addChild(
            $this->gNF,
            "CompetEmis",
            str_replace('-', '', $std->CompetEmis),
            true,
            $identificador . "Ano e mês da emissão da NF"
        );
        if ($std->hash115) {
            $this->dom->addChild(
                $this->gNF,
                "hash115",
                $std->hash115,
                false,
                $identificador . "Hash do registro no arquivo do convênio 115"
            );
        }
        $this->gCofat->appendChild($this->gNF);
        return $this->gNF;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagDet(stdClass $std)
    {
        $possible = [
            'item',
            'chNFComAnt',
            'nItemAnt',
            'indNFComAntPapelFatCentral',
            'infAdProd',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<det> - ';
        $det = $this->dom->createElement("det");
        $det->setAttribute("nItem", $std->item);
        $this->dom->addChild(
            $det,
            "chNFComAnt",
            $std->chNFComAnt,
            false,
            $identificador . "[item $std->item] Chave de Acesso da NFCom anterior"
        );
        $this->dom->addChild(
            $det,
            "nItemAnt",
            $std->nItemAnt,
            false,
            $identificador . "[item $std->item] Número do item da NFCom anterior"
        );
        $this->dom->addChild(
            $det,
            "indNFComAntPapelFatCentral",
            $std->indNFComAntPapelFatCentral,
            false,
            $identificador . "[item $std->item] Informa que a NFCom Anterior de Faturamento centralizado não é
            eletrônica"
        );
        $this->dom->addChild(
            $det,
            "infAdProd",
            $std->infAdProd,
            false,
            $identificador . "[item $std->item] Informações adicionais do produto
            (norma referenciada, informações complementares)"
        );
        $this->aDet[$std->item] = $det;
        return $det;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagProd(stdClass $std)
    {
        $possible = [
            'cProd',
            'xProd',
            'cClass',
            'CFOP',
            'CNPJLD',
            'uMed',
            'qFaturada',
            'vItem',
            'vDesc',
            'vOutro',
            'vProd',
            'dExpiracao',
            'indDevolucao',
            'indSemCST',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<prod> - ';
        $prod = $this->dom->createElement("prod");
        $this->dom->addChild(
            $prod,
            "cProd",
            $std->cProd,
            true,
            $identificador . "[item $std->item] Código do produto ou serviço."
        );
        $this->dom->addChild(
            $prod,
            "xProd",
            $std->xProd,
            true,
            $identificador . "[item $std->item] Descrição do produto ou serviço"
        );
        $this->dom->addChild(
            $prod,
            "cClass",
            $std->cClass,
            true,
            $identificador . "[item $std->item] Código de classificação"
        );
        $this->dom->addChild(
            $prod,
            "CFOP",
            $std->CFOP,
            false,
            $identificador . "[item $std->item] CFOP"
        );
        $this->dom->addChild(
            $prod,
            "CNPJLD",
            $std->CNPJLD,
            false,
            $identificador . "[item $std->item] CNPJ da operadora LD"
        );
        $this->dom->addChild(
            $prod,
            "uMed",
            $std->uMed,
            true,
            $identificador . "[item $std->item] Unidade Básica de Medida"
        );
        $this->dom->addChild(
            $prod,
            "qFaturada",
            $this->conditionalNumberFormatting($std->qFaturada, 4),
            true,
            $identificador . "[item $std->item] Quantidade Faturada"
        );
        $this->dom->addChild(
            $prod,
            "vItem",
            $this->conditionalNumberFormatting($std->vItem),
            true,
            $identificador . "[item $std->item] Valor unitário do item"
        );
        $this->dom->addChild(
            $prod,
            "vDesc",
            $this->conditionalNumberFormatting($std->vDesc),
            false,
            $identificador . "[item $std->item] Valor do Desconto"
        );
        $this->dom->addChild(
            $prod,
            "vOutro",
            $this->conditionalNumberFormatting($std->vOutro),
            false,
            $identificador . "[item $std->item] Outras despesas acessórias"
        );
        $this->dom->addChild(
            $prod,
            "vProd",
            $this->conditionalNumberFormatting($std->vProd),
            true,
            $identificador . "[item $std->item] Valor total do item"
        );
        $this->dom->addChild(
            $prod,
            "dExpiracao",
            $std->dExpiracao,
            false,
            $identificador . "[item $std->item] Data de expiração de crédito"
        );
        $this->dom->addChild(
            $prod,
            "indDevolucao",
            $std->indDevolucao,
            false,
            $identificador . "[item $std->item] Indicador de devolução do valor do item"
        );
        $this->dom->addChild(
            $prod,
            "indSemCST",
            $std->indSemCST,
            false,
            $identificador . "[item $std->item] Sem Situação Tributária para o ICMS"
        );
        $this->aProd[$std->item] = $prod;
        return $prod;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagICMS(stdClass $std)
    {
        $possible = [
            'item',
            'CST',
            'vBC',
            'pICMS',
            'vICMS',
            'pFCP',
            'vFCP',
            'pRedBC',
            'vICMSDeson',
            'cBenef',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<ICMSxx> - ';
        switch ($std->CST) {
            case '00':
                $icms = $this->dom->createElement("ICMS00");
                $this->dom->addChild(
                    $icms,
                    'CST',
                    $std->CST,
                    true,
                    "$identificador [item $std->item] Tributação do ICMS = 00"
                );
                $this->dom->addChild(
                    $icms,
                    'vBC',
                    $this->conditionalNumberFormatting($std->vBC),
                    true,
                    "$identificador [item $std->item] Valor da BC do ICMS"
                );
                $this->dom->addChild(
                    $icms,
                    'pICMS',
                    $this->conditionalNumberFormatting($std->pICMS),
                    true,
                    "$identificador [item $std->item] Alíquota do imposto"
                );
                $this->dom->addChild(
                    $icms,
                    'vICMS',
                    $this->conditionalNumberFormatting($std->vICMS),
                    true,
                    "$identificador [item $std->item] Valor do ICMS"
                );
                $this->dom->addChild(
                    $icms,
                    'pFCP',
                    $this->conditionalNumberFormatting($std->pFCP),
                    true,
                    "$identificador [item $std->item] Percentual de ICMS relativo ao Fundo de Combate à Pobreza"
                );
                $this->dom->addChild(
                    $icms,
                    'vFCP',
                    $this->conditionalNumberFormatting($std->vFCP),
                    true,
                    "$identificador [item $std->item] Valor do ICMS relativo ao Fundo de Combate à Pobreza"
                );
                break;

            case '20':
                $icms = $this->dom->createElement("ICMS20");
                $this->dom->addChild(
                    $icms,
                    'CST',
                    $std->CST,
                    true,
                    "$identificador [item $std->item] Tributação do ICMS = 20"
                );
                $this->dom->addChild(
                    $icms,
                    'pRedBC',
                    $this->conditionalNumberFormatting($std->pRedBC),
                    true,
                    "$identificador [item $std->item] Percentual da Redução de BC"
                );
                $this->dom->addChild(
                    $icms,
                    'vBC',
                    $this->conditionalNumberFormatting($std->vBC),
                    true,
                    "$identificador [item $std->item] Valor da BC do ICMS"
                );
                $this->dom->addChild(
                    $icms,
                    'pICMS',
                    $this->conditionalNumberFormatting($std->pICMS),
                    true,
                    "$identificador [item $std->item] Alíquota do imposto"
                );
                $this->dom->addChild(
                    $icms,
                    'vICMS',
                    $this->conditionalNumberFormatting($std->vICMS),
                    true,
                    "$identificador [item $std->item] Valor do ICMS"
                );
                $this->dom->addChild(
                    $icms,
                    'vICMSDeson',
                    $this->conditionalNumberFormatting($std->vICMSDeson),
                    true,
                    "$identificador [item $std->item] Valor do ICMS desonerado"
                );
                $this->dom->addChild(
                    $icms,
                    'cBenef',
                    $std->cBenef,
                    true,
                    "$identificador [item $std->item] Código de Benefício Fiscal na UF aplicado ao item"
                );
                $this->dom->addChild(
                    $icms,
                    'pFCP',
                    $this->conditionalNumberFormatting($std->pFCP),
                    true,
                    "$identificador [item $std->item] Percentual do Fundo de Combate à Pobreza"
                );
                $this->dom->addChild(
                    $icms,
                    'vFCP',
                    $this->conditionalNumberFormatting($std->vFCP),
                    true,
                    "$identificador [item $std->item] Valor do FCP"
                );
                break;

            case '40':
                $icms = $this->dom->createElement("ICMS40");
                $this->dom->addChild(
                    $icms,
                    'CST',
                    $std->CST,
                    true,
                    "$identificador [item $std->item] Tributação do ICMS $std->CST"
                );
                $this->dom->addChild(
                    $icms,
                    'vICMSDeson',
                    $this->conditionalNumberFormatting($std->vICMSDeson),
                    true,
                    "$identificador [item $std->item] Valor do ICMS desonerado"
                );
                $this->dom->addChild(
                    $icms,
                    'cBenef',
                    $std->cBenef,
                    true,
                    "$identificador [item $std->item] Código de Benefício Fiscal na UF aplicado ao item"
                );
                break;

            case '51':
                $icms = $this->dom->createElement("ICMS51");

                $this->dom->addChild(
                    $icms,
                    'CST',
                    $std->CST,
                    true,
                    "$identificador [item $std->item] Tributação do ICMS $std->CST"
                );
                $this->dom->addChild(
                    $icms,
                    'vICMSDeson',
                    $this->conditionalNumberFormatting($std->vICMSDeson),
                    false,
                    "$identificador [item $std->item] Valor do ICMS desonerado"
                );
                $this->dom->addChild(
                    $icms,
                    'cBenef',
                    $std->cBenef,
                    false,
                    "$identificador [item $std->item] Código de Benefício Fiscal na UF aplicado ao item"
                );
                break;

            case '90':
                $icms = $this->dom->createElement("ICMS90");
                $this->dom->addChild(
                    $icms,
                    'CST',
                    $std->CST,
                    true,
                    "$identificador [item $std->item] Tributação do ICMS = 90"
                );
                $this->dom->addChild(
                    $icms,
                    'vBC',
                    $this->conditionalNumberFormatting($std->vBC),
                    true,
                    "$identificador [item $std->item] Valor da BC do ICMS"
                );
                $this->dom->addChild(
                    $icms,
                    'pICMS',
                    $this->conditionalNumberFormatting($std->pICMS),
                    true,
                    "$identificador [item $std->item] Alíquota do imposto"
                );
                $this->dom->addChild(
                    $icms,
                    'vICMS',
                    $this->conditionalNumberFormatting($std->vICMS),
                    true,
                    "$identificador [item $std->item] Valor do ICMS"
                );
                $this->dom->addChild(
                    $icms,
                    'pFCP',
                    $this->conditionalNumberFormatting($std->pFCP),
                    true,
                    "$identificador [item $std->item] Percentual do Fundo de Combate à Pobreza"
                );
                $this->dom->addChild(
                    $icms,
                    'vFCP',
                    $this->conditionalNumberFormatting($std->vFCP),
                    true,
                    "$identificador [item $std->item] Valor do FCP"
                );
                break;
        }
        $this->aICMS[$std->item] = $icms;
        return $icms;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagICMSSN(stdClass $std)
    {
        $possible = [
            'item',
            'CST',
            'indSN'
        ];
        $std = $this->equilizeParameters($std, $possible);

        $icmsSN = $this->dom->createElement("ICMSSN");
        $this->dom->addChild(
            $icmsSN,
            'CST',
            $std->orig,
            true,
            "[item $std->item] Classificação Tributária do Serviço"
        );
        $this->dom->addChild(
            $icmsSN,
            'indSN',
            $std->CSOSN,
            true,
            "[item $std->item] Indica se o contribuinte é Simples Nacional 1=Sim"
        );
        $this->aICMSSN[$std->item] = $icmsSN;
        return $icmsSN;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagICMSUFDest(stdClass $std)
    {
        $possible = [
            'item',
            'cUFDest',
            'vBCUFDest',
            'pFCPUFDest',
            'pICMSUFDest',
            'vFCPUFDest',
            'vICMSUFDest',
            'vICMSUFEmi',
            'cBenefUFDest',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $ICMSUFDest = $this->dom->createElement("ICMSUFDest");
        $this->dom->addChild(
            $ICMSUFDest,
            'cUFDest',
            $std->cUFDest,
            true,
            "[item $std->item] Classificação Tributária do Serviço"
        );
        $this->dom->addChild(
            $ICMSUFDest,
            'vBCUFDest',
            $this->conditionalNumberFormatting($std->vBCUFDest),
            true,
            "[item $std->item] Valor da BC do ICMS na UF de destino "
        );
        $this->dom->addChild(
            $ICMSUFDest,
            'pFCPUFDest',
            $this->conditionalNumberFormatting($std->pFCPUFDest),
            true,
            "[item $std->item] Percentual do ICMS relativo ao Fundo de Combate à pobreza na UF de destino"
        );
        $this->dom->addChild(
            $ICMSUFDest,
            'pICMSUFDest',
            $this->conditionalNumberFormatting($std->pICMSUFDest),
            true,
            "[item $std->item] Alíquota interna da UF de destino"
        );
        $this->dom->addChild(
            $ICMSUFDest,
            'vFCPUFDest',
            $this->conditionalNumberFormatting($std->vFCPUFDest),
            true,
            "[item $std->item] Valor do ICMS relativo ao Fundo de Combate á Pobreza da UF de destino"
        );
        $this->dom->addChild(
            $ICMSUFDest,
            'vICMSUFDest',
            $this->conditionalNumberFormatting($std->vICMSUFDest),
            true,
            "[item $std->item] Valor do ICMS de partilha para a UF de destino"
        );
        $this->dom->addChild(
            $ICMSUFDest,
            'vICMSUFEmi',
            $this->conditionalNumberFormatting($std->vICMSUFEmi),
            true,
            "[item $std->item] Valor do ICMS de partilha para a UF de emissão"
        );
        $this->dom->addChild(
            $ICMSUFDest,
            'cBenefUFDest',
            $std->cBenefUFDest,
            false,
            "[item $std->item] Código de Benefício Fiscal na UF destino aplicado ao item"
        );
        $this->aICMSUFDest[$std->item][] = $ICMSUFDest;
        return $ICMSUFDest;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagPIS(stdClass $std)
    {
        $possible = [
            'item',
            'CST',
            'vBC',
            'pPIS',
            'vPIS',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $PIS = $this->dom->createElement("PIS");
        $this->dom->addChild(
            $PIS,
            'CST',
            $std->CST,
            true,
            "[item $std->item] classificação Tributária do PIS"
        );
        $this->dom->addChild(
            $PIS,
            'vBC',
            $this->conditionalNumberFormatting($std->vBC),
            true,
            "[item $std->item] Valor da BC do PIS"
        );
        $this->dom->addChild(
            $PIS,
            'pPIS',
            $this->conditionalNumberFormatting($std->pPIS),
            true,
            "[item $std->item] Alíquota do PIS (em percentual) "
        );
        $this->dom->addChild(
            $PIS,
            'vPIS',
            $this->conditionalNumberFormatting($std->vPIS),
            true,
            "[item $std->item] Valor do PIS"
        );
        $this->aPIS[$std->item] = $PIS;
        return $PIS;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagCOFINS(stdClass $std)
    {
        $possible = [
            'item',
            'CST',
            'vBC',
            'pCOFINS',
            'vCOFINS',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $COFINS = $this->dom->createElement("COFINS");
        $this->dom->addChild(
            $COFINS,
            'CST',
            $std->CST,
            true,
            "[item $std->item] classificação Tributária do COFINS"
        );
        $this->dom->addChild(
            $COFINS,
            'vBC',
            $this->conditionalNumberFormatting($std->vBC),
            true,
            "[item $std->item] Valor da BC do COFINS"
        );
        $this->dom->addChild(
            $COFINS,
            'pCOFINS',
            $this->conditionalNumberFormatting($std->pCOFINS),
            true,
            "[item $std->item] Alíquota do COFINS (em percentual) "
        );
        $this->dom->addChild(
            $COFINS,
            'vCOFINS',
            $this->conditionalNumberFormatting($std->vCOFINS),
            true,
            "[item $std->item] Valor do COFINS"
        );
        $this->aCOFINS[$std->item] = $COFINS;
        return $COFINS;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagFUST(stdClass $std)
    {
        $possible = [
            'item',
            'vBC',
            'pFUST',
            'vFUST',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $FUST = $this->dom->createElement("FUST");
        $this->dom->addChild(
            $FUST,
            'vBC',
            $this->conditionalNumberFormatting($std->vBC),
            true,
            "[item $std->item] Valor da BC do FUST"
        );
        $this->dom->addChild(
            $FUST,
            'pFUST',
            $this->conditionalNumberFormatting($std->pFUST),
            true,
            "[item $std->item] Alíquota do FUST (em percentual) "
        );
        $this->dom->addChild(
            $FUST,
            'vFUST',
            $this->conditionalNumberFormatting($std->vFUST),
            true,
            "[item $std->item] Valor do FUST"
        );
        $this->aFUST[$std->item] = $FUST;
        return $FUST;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagFUNTTEL(stdClass $std)
    {
        $possible = [
            'item',
            'vBC',
            'pFUNTTEL',
            'vFUNTTEL',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $FUNTTEL = $this->dom->createElement("FUNTTEL");
        $this->dom->addChild(
            $FUNTTEL,
            'vBC',
            $this->conditionalNumberFormatting($std->vBC),
            true,
            "[item $std->item] Valor da BC do FUNTTEL"
        );
        $this->dom->addChild(
            $FUNTTEL,
            'pFUNTTEL',
            $this->conditionalNumberFormatting($std->pFUNTTEL),
            true,
            "[item $std->item] Alíquota do FUNTTEL (em percentual) "
        );
        $this->dom->addChild(
            $FUNTTEL,
            'vFUNTTEL',
            $this->conditionalNumberFormatting($std->vFUNTTEL),
            true,
            "[item $std->item] Valor do FUNTTEL"
        );
        $this->aFUNTTEL[$std->item] = $FUNTTEL;
        return $FUNTTEL;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagRetTrib(stdClass $std)
    {
        $possible = [
            'item',
            'vRetPIS',
            'vRetCofins',
            'vRetCSLL',
            'vBCIRRF',
            'vIRRF',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $retTrib = $this->dom->createElement("retTrib");
        $this->dom->addChild(
            $retTrib,
            'vRetPIS',
            $this->conditionalNumberFormatting($std->vRetPIS),
            true,
            "[item $std->item] Valor do PIS retido"
        );
        $this->dom->addChild(
            $retTrib,
            'vRetCofins',
            $this->conditionalNumberFormatting($std->vRetCofins),
            true,
            "[item $std->item] Valor do COFNS retido"
        );
        $this->dom->addChild(
            $retTrib,
            'vRetCSLL',
            $this->conditionalNumberFormatting($std->vRetCSLL),
            true,
            "[item $std->item] Valor da CSLL retida"
        );
        $this->dom->addChild(
            $retTrib,
            'vBCIRRF',
            $this->conditionalNumberFormatting($std->vBCIRRF),
            true,
            "[item $std->item] Base de cálculo do IRRF"
        );
        $this->dom->addChild(
            $retTrib,
            'vIRRF',
            $this->conditionalNumberFormatting($std->vIRRF),
            true,
            "[item $std->item] Valor do IRRF retido"
        );
        $this->aRetTrib[$std->item] = $retTrib;
        return $retTrib;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagIBSCBS(stdClass $std)
    {
        $possible = [
            'item',
            'CST',
            'cClassTrib',
            'indDoacao',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<IBSCBS> - ';
        $IBSCBS = $this->dom->createElement("IBSCBS");
        $this->dom->addChild(
            $IBSCBS,
            'CST',
            $std->CST,
            true,
            $identificador . "[item $std->item] Código da Situação Tributária do IBS/CBS"
        );
        $this->dom->addChild(
            $IBSCBS,
            'cClassTrib',
            $std->cClassTrib,
            true,
            $identificador . "[item $std->item] Código da Classificação Tributária do IBS/CBS"
        );
        $this->dom->addChild(
            $IBSCBS,
            'indDoacao',
            $std->indDoacao,
            true,
            $identificador . "[item $std->item] Indica se a operação é de doação"
        );
        $this->aIBSCBS[$std->item] = $IBSCBS;
        return $IBSCBS;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGIBSCBS(stdClass $std)
    {
        $possible = [
            'item',
            'vBC',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gIBSCBS> - ';
        $gIBSCBS = $this->dom->createElement("gIBSCBS");
        $this->dom->addChild(
            $gIBSCBS,
            "vBC",
            $std->vBC,
            true,
            $identificador . "[item $std->item] Valor da Base de cálculo comum a IBS/CBS"
        );
        $this->aGIBSCBS[$std->item] = $gIBSCBS;
        return $gIBSCBS;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGIBSUF(stdClass $std)
    {
        $possible = [
            'item',
            'pIBSUF',
            'vIBSUF',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gIBSUF> - ';
        $gIBSUF = $this->dom->createElement("gIBSUF");
        $this->dom->addChild(
            $gIBSUF,
            "pIBSUF",
            $std->pIBSUF,
            true,
            $identificador . "[item $std->item] Alíquota do IBS Estadual"
        );
        $this->dom->addChild(
            $gIBSUF,
            "vIBSUF",
            $std->vIBSUF,
            true,
            $identificador . "[item $std->item] Valor do IBS de competência da UF"
        );
        $this->aGIBSUF[$std->item] = $gIBSUF;
        return $gIBSUF;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGDifGIBSUF(stdClass $std)
    {
        $possible = [
            'pDif',
            'vDif',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gDif> - ';
        if (empty($this->aGIBSUF)) {
            throw new RuntimeException('A TAG gIBSUF deve ser criada antes do endereço do mesmo.');
        }
        $this->gDif = $this->dom->createElement("gDif");
        $this->dom->addChild($this->gDif, "pDif", $std->pDif, true, $identificador . "Percentual de diferimento");
        $this->dom->addChild($this->gDif, "vDif", $std->vDif, true, $identificador . "Valor do diferimento");
        $this->aGIBSUF->appendChild($this->gDif);
        return $this->gDif;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGDevTribGIBSUF(stdClass $std)
    {
        $possible = [
            'vDevTrib',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gDevTrib> - ';
        if (empty($this->aGIBSUF)) {
            throw new RuntimeException('A TAG gIBSUF deve ser criada antes do endereço do mesmo.');
        }
        $this->gDevTrib = $this->dom->createElement("gDevTrib");
        $this->dom->addChild(
            $this->gDevTrib,
            "vDevTrib",
            $std->vDevTrib,
            true,
            $identificador . "Valor do tributo devolvido"
        );
        $this->aGIBSUF->appendChild($this->gDevTrib);
        return $this->gDevTrib;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGRedGIBSUF(stdClass $std)
    {
        $possible = [
            'pRedAliq',
            'pAliqEfet',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gRed> - ';
        if (empty($this->aGIBSUF)) {
            throw new RuntimeException('A TAG gIBSUF deve ser criada antes do endereço do mesmo.');
        }
        $this->gRed = $this->dom->createElement("gDevTrib");
        $this->dom->addChild(
            $this->gRed,
            "pRedAliq",
            $std->pRedAliq,
            true,
            $identificador . "Percentual da redução de Alíquota do cClassTrib"
        );
        $this->dom->addChild(
            $this->gRed,
            "pAliqEfet",
            $std->pAliqEfet,
            true,
            $identificador . "Alíquota efetiva do IBS de competência das UF que será aplicada a base de cálculo"
        );
        $this->aGIBSUF->appendChild($this->gRed);
        return $this->gRed;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGIBSMun(stdClass $std)
    {
        $possible = [
            'item',
            'pIBSMun',
            'vIBSMun',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gIBSMun> - ';
        $gIBSMun = $this->dom->createElement("gIBSMun");
        $this->dom->addChild(
            $gIBSMun,
            "pIBSMun",
            $std->pIBSMun,
            true,
            $identificador . "[item $std->item] Alíquota do IBS Estadual"
        );
        $this->dom->addChild(
            $gIBSMun,
            "vIBSMun",
            $std->vIBSMun,
            true,
            $identificador . "[item $std->item] Valor do IBS de competência da UF"
        );
        $this->aGIBSMun[$std->item] = $gIBSMun;
        return $gIBSMun;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGDifGIBSMun(stdClass $std)
    {
        $possible = [
            'pDif',
            'vDif',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gDif> - ';
        if (empty($this->aGIBSMun)) {
            throw new RuntimeException('A TAG gIBSMun deve ser criada antes do endereço do mesmo.');
        }
        $this->gDif = $this->dom->createElement("gDif");
        $this->dom->addChild($this->gDif, "pDif", $std->pDif, true, $identificador . "Percentual de diferimento");
        $this->dom->addChild($this->gDif, "vDif", $std->vDif, true, $identificador . "Valor do diferimento");
        $this->aGIBSMun->appendChild($this->gDif);
        return $this->gDif;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGDevTribGIBSMun(stdClass $std)
    {
        $possible = [
            'vDevTrib',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gDevTrib> - ';
        if (empty($this->aGIBSMun)) {
            throw new RuntimeException('A TAG gIBSMun deve ser criada antes do endereço do mesmo.');
        }
        $this->gDevTrib = $this->dom->createElement("gDevTrib");
        $this->dom->addChild(
            $this->gDevTrib,
            "vDevTrib",
            $std->vDevTrib,
            true,
            $identificador . "Valor do tributo devolvido"
        );
        $this->aGIBSMun->appendChild($this->gDevTrib);
        return $this->gDevTrib;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGRedGIBSMun(stdClass $std)
    {
        $possible = [
            'pRedAliq',
            'pAliqEfet',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gRed> - ';
        if (empty($this->aGIBSMun)) {
            throw new RuntimeException('A TAG gIBSMun deve ser criada antes do endereço do mesmo.');
        }
        $this->gRed = $this->dom->createElement("gDevTrib");
        $this->dom->addChild(
            $this->gRed,
            "pRedAliq",
            $std->pRedAliq,
            true,
            $identificador . "Percentual da redução de Alíquota do cClassTrib"
        );
        $this->dom->addChild(
            $this->gRed,
            "pAliqEfet",
            $std->pAliqEfet,
            true,
            $identificador . "Alíquota efetiva do IBS de competência das UF que será aplicada a base de cálculo"
        );
        $this->aGIBSMun->appendChild($this->gRed);
        return $this->gRed;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGCBS(stdClass $std)
    {
        $possible = [
            'item',
            'pCBS',
            'vCBS',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gCBS> - ';
        $gCBS = $this->dom->createElement("gCBS");
        $this->dom->addChild(
            $gCBS,
            "pCBS",
            $std->pCBS,
            true,
            $identificador . "[item $std->item] Alíquota da CBS"
        );
        $this->dom->addChild(
            $gCBS,
            "vCBS",
            $std->vCBS,
            true,
            $identificador . "[item $std->item] Valor da CBS"
        );
        $this->aGCBS[$std->item] = $gCBS;
        return $gCBS;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGDifGCBS(stdClass $std)
    {
        $possible = [
            'pDif',
            'vDif',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gDif> - ';
        if (empty($this->aGCBS)) {
            throw new RuntimeException('A TAG aGCBS deve ser criada antes do endereço do mesmo.');
        }
        $this->gDif = $this->dom->createElement("gDif");
        $this->dom->addChild($this->gDif, "pDif", $std->pDif, true, $identificador . "Percentual de diferimento");
        $this->dom->addChild($this->gDif, "vDif", $std->vDif, true, $identificador . "Valor do diferimento");
        $this->aGCBS->appendChild($this->gDif);
        return $this->gDif;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGDevTribGCBS(stdClass $std)
    {
        $possible = [
            'vDevTrib',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gDevTrib> - ';
        if (empty($this->aGCBS)) {
            throw new RuntimeException('A TAG aGCBS deve ser criada antes do endereço do mesmo.');
        }
        $this->gDevTrib = $this->dom->createElement("gDevTrib");
        $this->dom->addChild(
            $this->gDevTrib,
            "vDevTrib",
            $std->vDevTrib,
            true,
            $identificador . "Valor do tributo devolvido"
        );
        $this->aGCBS->appendChild($this->gDevTrib);
        return $this->gDevTrib;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGRedGCBS(stdClass $std)
    {
        $possible = [
            'pRedAliq',
            'pAliqEfet',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gRed> - ';
        if (empty($this->aGCBS)) {
            throw new RuntimeException('A TAG aGCBS deve ser criada antes do endereço do mesmo.');
        }
        $this->gRed = $this->dom->createElement("gDevTrib");
        $this->dom->addChild(
            $this->gRed,
            "pRedAliq",
            $std->pRedAliq,
            true,
            $identificador . "Percentual da redução de Alíquota do cClassTrib"
        );
        $this->dom->addChild(
            $this->gRed,
            "pAliqEfet",
            $std->pAliqEfet,
            true,
            $identificador . "Alíquota efetiva do IBS de competência das UF que será aplicada a base de cálculo"
        );
        $this->aGCBS->appendChild($this->gRed);
        return $this->gRed;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGTribRegular(stdClass $std)
    {
        $possible = [
            'item',
            'CSTReg',
            'cClassTribReg',
            'pAliqEfetRegIBSUF',
            'pAliqEfetRegIBSMun',
            'vTribRegIBSMun',
            'pAliqEfetRegCBS',
            'vTribRegCBS',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gTribRegular> - ';
        $gTribRegular = $this->dom->createElement("gTribRegular");
        $this->dom->addChild(
            $gTribRegular,
            "CSTReg",
            $std->CSTReg,
            true,
            $identificador . "[item $std->item] Código da Situação Tributária Informado como seria o CST
            caso não cumprida a condição resolutória/suspensiva"
        );
        $this->dom->addChild(
            $gTribRegular,
            "cClassTribReg",
            $std->cClassTribReg,
            true,
            $identificador . "[item $std->item] Código de Classificação Tributária Informado como seria o cClassTrib
            caso não cumprida a condição resolutória/suspensiva"
        );
        $this->dom->addChild(
            $gTribRegular,
            "pAliqEfetRegIBSUF",
            $std->pAliqEfetRegIBSUF,
            true,
            $identificador . "[item $std->item] Alíquota efetiva da UF Informado a Alíquota caso não cumprida a
            condição resolutória/suspensiva"
        );
        $this->dom->addChild(
            $gTribRegular,
            "vTribRegIBSUF",
            $std->vTribRegIBSUF,
            true,
            $identificador . "[item $std->item] Informado como seria o valor do Tributo da UF caso não cumprida a
            condição resolutória/suspensiva"
        );
        $this->dom->addChild(
            $gTribRegular,
            "pAliqEfetRegIBSMun",
            $std->pAliqEfetRegIBSMun,
            true,
            $identificador . "[item $std->item] Alíquota efetiva do Município Informado a Alíquota caso não cumprida a
            condição resolutória/suspensiva"
        );
        $this->dom->addChild(
            $gTribRegular,
            "vTribRegIBSMun",
            $std->vTribRegIBSMun,
            true,
            $identificador . "[item $std->item] Informado como seria o valor do Tributo do Município caso não cumprida
            a condição resolutória/suspensiva"
        );
        $this->dom->addChild(
            $gTribRegular,
            "pAliqEfetRegCBS",
            $std->pAliqEfetRegCBS,
            true,
            $identificador . "[item $std->item] Alíquota efetiva da CBS Informado a Alíquota caso não cumprida a
            condição resolutória/suspensiva"
        );
        $this->dom->addChild(
            $gTribRegular,
            "vTribRegCBS",
            $std->vTribRegCBS,
            true,
            $identificador . "[item $std->item] Informado como seria o valor do Tributo CBS caso não cumprida a
            condição resolutória/suspensiva"
        );
        $this->aGTribRegular[$std->item] = $gTribRegular;
        return $gTribRegular;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGIBSCredPres(stdClass $std)
    {
        $possible = [
            'item',
            'cCredPres',
            'pCredPres',
            'vCredPres',
            'vCredPresCondSus',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gIBSCredPres> - ';
        $gIBSCredPres = $this->dom->createElement("gIBSCredPres");
        $this->dom->addChild(
            $gIBSCredPres,
            "cCredPres",
            $std->cCredPres,
            true,
            $identificador . "[item $std->item] Código do Crédito Presumido (ver Tabela)"
        );
        $this->dom->addChild(
            $gIBSCredPres,
            "pCredPres",
            $std->pCredPres,
            true,
            $identificador . "[item $std->item] Percentual de crédito presumido"
        );
        $this->dom->addChild(
            $gIBSCredPres,
            "vCredPres",
            $std->vCredPres,
            true,
            $identificador . "[item $std->item] Valor do crédito presumido"
        );
        $this->dom->addChild(
            $gIBSCredPres,
            "vCredPresCondSus",
            $std->vCredPresCondSus,
            true,
            $identificador . "[item $std->item] Valor do Crédito Presumido Condição Suspensiva"
        );
        $this->aGIBSCredPres[$std->item] = $gIBSCredPres;
        return $gIBSCredPres;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGCBSCredPres(stdClass $std)
    {
        $possible = [
            'item',
            'cCredPres',
            'pCredPres',
            'vCredPres',
            'vCredPresCondSus',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gCBSCredPres> - ';
        $gCBSCredPres = $this->dom->createElement("gCBSCredPres");
        $this->dom->addChild(
            $gCBSCredPres,
            "cCredPres",
            $std->cCredPres,
            true,
            $identificador . "[item $std->item] Código do Crédito Presumido (ver Tabela)"
        );
        $this->dom->addChild(
            $gCBSCredPres,
            "pCredPres",
            $std->pCredPres,
            true,
            $identificador . "[item $std->item] Percentual de crédito presumido"
        );
        $this->dom->addChild(
            $gCBSCredPres,
            "vCredPres",
            $std->vCredPres,
            true,
            $identificador . "[item $std->item] Valor do crédito presumido"
        );
        $this->dom->addChild(
            $gCBSCredPres,
            "vCredPresCondSus",
            $std->vCredPresCondSus,
            true,
            $identificador . "[item $std->item] Valor do Crédito Presumido Condição Suspensiva"
        );
        $this->aGCBSCredPres[$std->item] = $gCBSCredPres;
        return $gCBSCredPres;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGTribCompraGov(stdClass $std)
    {
        $possible = [
            'item',
            'pAliqIBSUF',
            'vTribBSUF',
            'pAliqIBSMun',
            'vTribIBSMun',
            'pAliqCBS',
            'vTribCBS',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gTribCompraGov> - ';
        $gTribCompraGov = $this->dom->createElement("gTribCompraGov");
        $this->dom->addChild(
            $gTribCompraGov,
            "pAliqIBSUF",
            $std->pAliqIBSUF,
            true,
            $identificador . "[item $std->item] Alíquota IBS da UF utilizada"
        );
        $this->dom->addChild(
            $gTribCompraGov,
            "vTribBSUF",
            $std->vTribBSUF,
            true,
            $identificador . "[item $std->item] Valor do Tributo do IBS da UF"
        );
        $this->dom->addChild(
            $gTribCompraGov,
            "pAliqIBSMun",
            $std->pAliqIBSMun,
            true,
            $identificador . "[item $std->item] Alíquota IBS do Município utilizada"
        );
        $this->dom->addChild(
            $gTribCompraGov,
            "vTribIBSMun",
            $std->vTribIBSMun,
            true,
            $identificador . "[item $std->item] Valor do Tributo do Município da UF"
        );
        $this->dom->addChild(
            $gTribCompraGov,
            "pAliqCBS",
            $std->pAliqCBS,
            true,
            $identificador . "[item $std->item] Alíquota IBS do CBS utilizada"
        );
        $this->dom->addChild(
            $gTribCompraGov,
            "vTribCBS",
            $std->vTribCBS,
            true,
            $identificador . "[item $std->item] Valor do Tributo da CBS"
        );
        $this->aGTribCompraGov[$std->item] = $gTribCompraGov;
        return $gTribCompraGov;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGProcRef(stdClass $std)
    {
        $possible = [
            'item',
            'vItem',
            'qFaturada',
            'vProd',
            'vDesc',
            'vOutro',
            'indDevolucao',
            'vBC',
            'pICMS',
            'vICMS',
            'vPIS',
            'vCOFINS',
            'vFCP',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gProcRef> - ';
        $gProcRef = $this->dom->createElement("gProcRef");
        $this->dom->addChild(
            $gProcRef,
            "vItem",
            $this->conditionalNumberFormatting($std->vItem),
            true,
            $identificador . "[item $std->item] Valor unitário do item "
        );
        $this->dom->addChild(
            $gProcRef,
            "qFaturada",
            $this->conditionalNumberFormatting($std->qFaturada),
            true,
            $identificador . "[item $std->item] Quantidade Faturada"
        );
        $this->dom->addChild(
            $gProcRef,
            "vProd",
            $this->conditionalNumberFormatting($std->vProd),
            true,
            $identificador . "[item $std->item] Valor total do item"
        );
        $this->dom->addChild(
            $gProcRef,
            "vDesc",
            $this->conditionalNumberFormatting($std->vDesc),
            false,
            $identificador . "[item $std->item] Valor do Desconto"
        );
        $this->dom->addChild(
            $gProcRef,
            "vOutro",
            $this->conditionalNumberFormatting($std->vOutro),
            false,
            $identificador . "[item $std->item] Outras despesas acessórias"
        );
        $this->dom->addChild(
            $gProcRef,
            "indDevolucao",
            $std->indDevolucao,
            false,
            $identificador . "[item $std->item] Indicador de devolução do valor do item"
        );
        $this->dom->addChild(
            $gProcRef,
            "vBC",
            $this->conditionalNumberFormatting($std->vBC),
            false,
            $identificador . "[item $std->item] Valor da BC do ICMS"
        );
        $this->dom->addChild(
            $gProcRef,
            "pICMS",
            $this->conditionalNumberFormatting($std->pICMS),
            false,
            $identificador . "[item $std->item] Alíquota do ICMS"
        );
        $this->dom->addChild(
            $gProcRef,
            "vICMS",
            $this->conditionalNumberFormatting($std->vICMS),
            false,
            $identificador . "[item $std->item] Valor do ICMS"
        );
        $this->dom->addChild(
            $gProcRef,
            "vPIS",
            $this->conditionalNumberFormatting($std->vPIS),
            false,
            $identificador . "[item $std->item] Valor do PIS"
        );
        $this->dom->addChild(
            $gProcRef,
            "vCOFINS",
            $this->conditionalNumberFormatting($std->vCOFINS),
            false,
            $identificador . "[item $std->item] Valor do COFINS"
        );
        $this->dom->addChild(
            $gProcRef,
            "vFCP",
            $this->conditionalNumberFormatting($std->vFCP),
            false,
            $identificador . "[item $std->item] 	Valor do Fundo de Combate à Pobreza (FCP)"
        );
        $this->aGProcRef[$std->item] = $gProcRef;
        return $gProcRef;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGProc(stdClass $std)
    {
        $possible = [
            'item',
            'tpProc',
            'nProcesso',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gProc> - ';
        $gProc = $this->dom->createElement("gProc");
        $this->dom->addChild(
            $gProc,
            "tpProc",
            $std->tpProc,
            true,
            $identificador . "[item $std->item] Tipo de Processo"
        );
        $this->dom->addChild(
            $gProc,
            "nProcesso",
            $std->nProcesso,
            true,
            $identificador . "[item $std->item] Número do Processo"
        );
        $this->aGProc[$std->item] = $gProc;
        return $gProc;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGRessarc(stdClass $std)
    {
        $possible = [
            'item',
            'tpRessarc',
            'dRef',
            'nProcesso',
            'nProtReclama',
            'xObs'
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gRessarc> - ';
        $gRessarc = $this->dom->createElement("gRessarc");
        $this->dom->addChild(
            $gRessarc,
            "tpRessarc",
            $std->tpRessarc,
            true,
            $identificador . "[item $std->item] Tipo de Ressarcimento"
        );
        $this->dom->addChild(
            $gRessarc,
            "dRef",
            $std->dRef,
            true,
            $identificador . "[item $std->item] Data de referência"
        );
        $this->dom->addChild(
            $gRessarc,
            "nProcesso",
            $std->nProcesso,
            false,
            $identificador . "[item $std->item] Número do Processo"
        );
        $this->dom->addChild(
            $gRessarc,
            "nProtReclama",
            $std->nProtReclama,
            false,
            $identificador . "[item $std->item] Número do protocolo de reclamação"
        );
        $this->dom->addChild(
            $gRessarc,
            "xObs",
            $std->xObs,
            false,
            $identificador . "[item $std->item] Observações sobre o processo de ressarcimento"
        );
        $this->aGRessarc[$std->item] = $gRessarc;
        return $gRessarc;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagTotal(stdClass $std)
    {
        $possible = [
            'vProd',
            'vCOFINS',
            'vPIS',
            'vFUNTTEL',
            'vFUST',
            'vDesc',
            'vOutro',
            'vNF',
            'vTotDFe',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<total> - ';
        $this->total = $this->dom->createElement("total");
        $this->dom->addChild(
            $this->total,
            "vProd",
            $this->conditionalNumberFormatting($std->vProd),
            true,
            $identificador . "Valor Total dos produtos e serviços"
        );
        $this->dom->addChild(
            $this->total,
            "vCOFINS",
            $this->conditionalNumberFormatting($std->vCOFINS),
            true,
            "Valor da COFINS"
        );
        $this->dom->addChild(
            $this->total,
            "vPIS",
            $this->conditionalNumberFormatting($std->vPIS),
            true,
            "Valor do PIS"
        );
        $this->dom->addChild(
            $this->total,
            "vFUNTTEL",
            $this->conditionalNumberFormatting($std->vFUNTTEL),
            true,
            "Valor do FUNTTEL"
        );
        $this->dom->addChild(
            $this->total,
            "vFUST",
            $this->conditionalNumberFormatting($std->vFUST),
            true,
            "Valor do FUST"
        );
        $this->dom->addChild(
            $this->total,
            "vDesc",
            $this->conditionalNumberFormatting($std->vDesc),
            true,
            "Valor Total do Desconto"
        );
        $this->dom->addChild(
            $this->total,
            "vOutro",
            $this->conditionalNumberFormatting($std->vOutro),
            true,
            "Outras Despesas acessórias"
        );
        $this->dom->addChild(
            $this->total,
            "vNF",
            $this->conditionalNumberFormatting($std->vNF),
            true,
            "Valor Total da NFCom"
        );
        $this->dom->addChild(
            $this->total,
            "vTotDFe",
            $this->conditionalNumberFormatting($std->vTotDFe),
            true,
            "Valor total do documento fiscal (vNF + total do IBS + total da CBS)"
        );
        return $this->total;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagICMSTot(stdClass $std)
    {
        $possible = [
            'vBC',
            'vICMS',
            'vICMSDeson',
            'vFCP',

        ];
        if (isset($std)) {
            $std = $this->equilizeParameters($std, $possible);
        }

        if (empty($this->total)) {
            throw new RuntimeException('A TAG total deve ser criada antes do ICMSTot do mesmo.');
        }

        $ICMSTot = $this->dom->createElement("ICMSTot");
        $this->dom->addChild(
            $ICMSTot,
            "vBC",
            $this->conditionalNumberFormatting($std->vBC),
            true,
            "Base de Cálculo do ICMS"
        );
        $this->dom->addChild(
            $ICMSTot,
            "vICMS",
            $this->conditionalNumberFormatting($std->vICMS),
            true,
            "Valor Total do ICMS"
        );
        $this->dom->addChild(
            $ICMSTot,
            "vICMSDeson",
            $this->conditionalNumberFormatting($std->vICMSDeson),
            true,
            "Valor Total do ICMS desonerado"
        );
        $this->dom->addChild(
            $ICMSTot,
            "vFCP",
            $this->conditionalNumberFormatting($std->vFCP),
            false,
            "Valor total do ICMS relativo ao Fundo de Combate à Pobreza(FCP)"
        );

        $node = $this->total->getElementsByTagName("vCOFINS")->item(0);
        $this->total->insertBefore($ICMSTot, $node);
        return $ICMSTot;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagVRetTribTot(stdClass $std)
    {
        $possible = [
            'vRetPIS',
            'vRetCofins',
            'vRetCSLL',
            'vIRRF',

        ];
        if (isset($std)) {
            $std = $this->equilizeParameters($std, $possible);
        }

        if (empty($this->total)) {
            throw new RuntimeException('A TAG total deve ser criada antes do vRetTribTot do mesmo.');
        }

        $vRetTribTot = $this->dom->createElement("vRetTribTot");
        $this->dom->addChild(
            $vRetTribTot,
            "vRetPIS",
            $this->conditionalNumberFormatting($std->vRetPIS),
            true,
            "Valor do PIS retido"
        );
        $this->dom->addChild(
            $vRetTribTot,
            "vRetCofins",
            $this->conditionalNumberFormatting($std->vRetCofins),
            true,
            "Valor do COFINS retido"
        );
        $this->dom->addChild(
            $vRetTribTot,
            "vRetCSLL",
            $this->conditionalNumberFormatting($std->vRetCSLL),
            true,
            "Valor da CSLL retida"
        );
        $this->dom->addChild(
            $vRetTribTot,
            "vIRRF",
            $this->conditionalNumberFormatting($std->vIRRF),
            false,
            "Valor do IRRF retido"
        );

        $node = $this->total->getElementsByTagName("vDesc")->item(0);
        $this->total->insertBefore($vRetTribTot, $node);
        return $vRetTribTot;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagIBSCBSTot(stdClass $std)
    {
        $possible = [
            'vBCIBSCBS',
        ];
        if (isset($std)) {
            $std = $this->equilizeParameters($std, $possible);
        }

        if (empty($this->total)) {
            throw new RuntimeException('A TAG total deve ser criada antes do IBSCBSTot do mesmo.');
        }

        $IBSCBSTot = $this->dom->createElement("IBSCBSTot");
        $this->dom->addChild(
            $IBSCBSTot,
            "vBCIBSCBS",
            $this->conditionalNumberFormatting($std->vBCIBSCBS),
            true,
            "Total Base de Calculo"
        );

        $node = $this->total->getElementsByTagName("vTotDFe")->item(0);
        $this->total->insertBefore($IBSCBSTot, $node);
        return $IBSCBSTot;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagIBSTot(stdClass $std)
    {
        $possible = [
            'vIBS',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gIBS> - ';
        $gIBS = $this->dom->createElement("gIBS");
        $this->dom->addChild(
            $gIBS,
            "vIBS",
            $std->vIBS,
            true,
            $identificador . "	Valor total do IBS"
        );

        $node = $this->total->getElementsByTagName('IBSCBSTot')->item(0);
        $this->dom->appChild($node, $gIBS, 'Falta tag "IBSCBSTot"');
        return $gIBS;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagIBSUFTot(stdClass $std)
    {
        $possible = [
            'vDif',
            'vDevTrib',
            'vIBSUF',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gIBSUF> - ';
        $gIBSUF = $this->dom->createElement("gIBSUF");
        $this->dom->addChild(
            $gIBSUF,
            "vDif",
            $std->vDif,
            true,
            $identificador . "	Total do Diferimento"
        );
        $this->dom->addChild(
            $gIBSUF,
            "vDevTrib",
            $std->vDevTrib,
            true,
            $identificador . "	Total de devoluções de tributos"
        );
        $this->dom->addChild(
            $gIBSUF,
            "vIBSUF",
            $std->vIBSUF,
            true,
            $identificador . "	Valor total do IBS Estadual"
        );

        $node = $this->total->getElementsByTagName('gIBS')->item(0);
        $firstChild = $node->firstChild;
        if ($firstChild) {
            $node->insertBefore($gIBSUF, $firstChild);
        } else {
            $this->dom->appChild($node, $gIBSUF, 'Falta tag "gIBS"');
        }
        return $gIBSUF;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagIBMunTot(stdClass $std)
    {
        $possible = [
            'vDif',
            'vDevTrib',
            'vIBSMun',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gIBSMun> - ';
        $gIBSMun = $this->dom->createElement("gIBSMun");
        $this->dom->addChild(
            $gIBSMun,
            "vDif",
            $std->vDif,
            true,
            $identificador . "	Total do Diferimento"
        );
        $this->dom->addChild(
            $gIBSMun,
            "vDevTrib",
            $std->vDevTrib,
            true,
            $identificador . "	Total de devoluções de tributos"
        );
        $this->dom->addChild(
            $gIBSMun,
            "vIBSMun",
            $std->vIBSMun,
            true,
            $identificador . "	Valor total do IBS Municipal"
        );

        $node = $this->total->getElementsByTagName('gIBS')->item(0);
        $firstChild = $node->firstChild;
        if ($firstChild) {
            $node->insertBefore($gIBSMun, $firstChild);
        } else {
            $this->dom->appChild($node, $gIBSMun, 'Falta tag "gIBS"');
        }
        return $gIBSMun;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagCBSTot(stdClass $std)
    {
        $possible = [
            'vDif',
            'vDevTrib',
            'vCBS',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gCBS> - ';
        $gCBS = $this->dom->createElement("gCBS");
        $this->dom->addChild(
            $gCBS,
            "vDif",
            $std->vDif,
            true,
            $identificador . "	Total do Diferimento"
        );
        $this->dom->addChild(
            $gCBS,
            "vDevTrib",
            $std->vDevTrib,
            true,
            $identificador . "	Total de devoluções de tributos"
        );
        $this->dom->addChild(
            $gCBS,
            "vCBS",
            $std->vCBS,
            true,
            $identificador . "	Valor total da CBS"
        );

        $node = $this->total->getElementsByTagName('IBSCBSTot')->item(0);
        $this->dom->appChild($node, $gCBS, 'Falta tag "IBSCBSTot"');
        return $gCBS;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagEstornoCred(stdClass $std)
    {
        $possible = [
            'vIBSEstCred',
            'vCBSEstCred',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gEstornoCred> - ';
        $gEstornoCred = $this->dom->createElement("gEstornoCred");
        $this->dom->addChild(
            $gEstornoCred,
            "vIBSEstCred",
            $std->vIBSEstCred,
            true,
            $identificador . "	Valor total do IBS estornado"
        );
        $this->dom->addChild(
            $gEstornoCred,
            "vCBSEstCred",
            $std->vCBSEstCred,
            true,
            $identificador . "	Valor total da CBS estornada"
        );

        $node = $this->total->getElementsByTagName('IBSCBSTot')->item(0);
        $this->dom->appChild($node, $gEstornoCred, 'Falta tag "IBSCBSTot"');
        return $gEstornoCred;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGFidelidade(stdClass $std)
    {
        $possible = [
            'qtdSaldoPts',
            'dRefSaldoPts',
            'qtdPtsResg',
            'dRefResgPts',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gFidelidade> - ';
        $this->gFidelidade = $this->dom->createElement("gFidelidade");
        $this->dom->addChild(
            $this->gFidelidade,
            "qtdSaldoPts",
            $std->qtdSaldoPts,
            true,
            $identificador . "Saldo de pontos do cliente na data de referência"
        );
        $this->dom->addChild(
            $this->gFidelidade,
            "dRefSaldoPts",
            $std->dRefSaldoPts,
            true,
            $identificador . "Data de aferição do saldo de pontos"
        );
        $this->dom->addChild(
            $this->gFidelidade,
            "qtdPtsResg",
            $std->qtdPtsResg,
            true,
            $identificador . "Qtd de pontos resgatados na data de referência"
        );
        $this->dom->addChild(
            $this->gFidelidade,
            "dRefResgPts",
            $std->dRefResgPts,
            true,
            $identificador . "Data de resgate dos pontos"
        );
        return $this->gFidelidade;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGFat(stdClass $std)
    {
        $possible = [
            'CompetFat',
            'dVencFat',
            'dPerUsoIni',
            'dPerUsoFim',
            'codBarras',
            'codDebAuto',
            'codBanco',
            'codAgencia',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gFat> - ';
        $this->gFat = $this->dom->createElement("gFat");
        $this->dom->addChild(
            $this->gFat,
            "CompetFat",
            str_replace('-', '', $std->CompetFat),
            true,
            $identificador . "Ano e mês referência do faturamento"
        );
        $this->dom->addChild(
            $this->gFat,
            "dVencFat",
            $std->dVencFat,
            true,
            $identificador . "Data de vencimento da fatura"
        );
        if ($std->dPerUsoIni) {
            $this->dom->addChild(
                $this->gFat,
                "dPerUsoIni",
                $std->dPerUsoIni,
                true,
                $identificador . "Período de uso inicial"
            );
        }
        if ($std->dPerUsoFim) {
            $this->dom->addChild(
                $this->gFat,
                "dPerUsoFim",
                $std->dPerUsoFim,
                true,
                $identificador . "Período de uso final"
            );
        }
        if ($std->codBarras) {
            $this->dom->addChild(
                $this->gFat,
                "codBarras",
                $std->codBarras,
                true,
                $identificador . "Linha digitável do código de barras"
            );
        }
        if ($std->codDebAuto) {
            $this->dom->addChild(
                $this->gFat,
                "codDebAuto",
                $std->codDebAuto,
                false,
                $identificador . "Código de autorização débito em conta"
            );
        }
        if ($std->codBanco) {
            $this->dom->addChild(
                $this->gFat,
                "codBanco",
                $std->codBanco,
                true,
                $identificador . "Número do banco para débito em conta"
            );
        }
        if ($std->codAgencia) {
            $this->dom->addChild(
                $this->gFat,
                "codAgencia",
                $std->codAgencia,
                true,
                $identificador . "Número da agência bancária para débito em conta"
            );
        }
        return $this->gFat;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGFatEnderCorresp(stdClass $std)
    {
        $possible = [
            'xLgr',
            'nro',
            'xCpl',
            'xBairro',
            'cMun',
            'xMun',
            'CEP',
            'UF',
            'fone',
            'email',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<enderCorresp> - ';
        $enderCorresp = $this->dom->createElement("enderCorresp");
        $this->dom->addChild(
            $enderCorresp,
            "xLgr",
            $std->xLgr,
            true,
            $identificador . "Logradouro do Endereço da Fatura"
        );
        $this->dom->addChild(
            $enderCorresp,
            "nro",
            $std->nro,
            true,
            $identificador . "Número do Endereço da Fatura"
        );
        $this->dom->addChild(
            $enderCorresp,
            "xCpl",
            $std->xCpl,
            false,
            $identificador . "Complemento do Endereço da Fatura"
        );
        $this->dom->addChild(
            $enderCorresp,
            "xBairro",
            $std->xBairro,
            true,
            $identificador . "Bairro do Endereço da Fatura"
        );
        $this->dom->addChild(
            $enderCorresp,
            "cMun",
            $std->cMun,
            true,
            $identificador . "Código do município do Endereço da Fatura"
        );
        $this->dom->addChild(
            $enderCorresp,
            "xMun",
            $std->xMun,
            true,
            $identificador . "Nome do município do Endereço da Fatura"
        );
        $this->dom->addChild(
            $enderCorresp,
            "CEP",
            $std->CEP,
            true,
            $identificador . "CEP do Endereço da Fatura"
        );
        $this->dom->addChild(
            $enderCorresp,
            "UF",
            $std->UF,
            true,
            $identificador . "Sigla da UF do Endereço da Fatura"
        );
        $this->dom->addChild(
            $enderCorresp,
            "fone",
            $std->fone,
            false,
            $identificador . "Telefone do Endereço da Fatura"
        );
        $this->dom->addChild(
            $enderCorresp,
            "email",
            $std->email,
            false,
            $identificador . "Endereço de E-mail do Endereço da Fatura"
        );
        $this->dom->appChild($this->gFat, $enderCorresp, 'Falta tag "gFat"');
        return $enderCorresp;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGFatGPIX(stdClass $std)
    {
        $possible = [
            'urlQRCodePIX',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gPIX> - ';
        $gPIX = $this->dom->createElement("gPIX");
        $this->dom->addChild(
            $gPIX,
            "urlQRCodePIX",
            $std->urlQRCodePIX,
            true,
            $identificador . "URL do QRCode do PIX que será apresentado na fatura"
        );
        $this->dom->appChild($this->gFat, $gPIX, 'Falta tag "gFat"');
        return $gPIX;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGFatCentral(stdClass $std)
    {
        $possible = ['CNPJ','cUF'];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gFatCentral> - ';
        $this->gFatCentral = $this->dom->createElement("gFatCentral");
        $this->dom->addChild(
            $this->gFatCentral,
            "CNPJ",
            $std->CNPJ,
            true,
            $identificador . "CNPJ do Emitente centralizador"
        );
        $this->dom->addChild(
            $this->gFatCentral,
            "cUF",
            $std->cUF,
            true,
            $identificador . "Código da UF do emitente centralizador"
        );

        return $this->gFatCentral;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagautXML(stdClass $std)
    {
        $possible = ['CNPJ', 'CPF'];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<autXML> - ';
        $std->CNPJ = !empty($std->CNPJ) ? $std->CNPJ : null;
        $std->CPF = !empty($std->CPF) ? $std->CPF : null;
        $autXML = $this->dom->createElement("autXML");
        $this->dom->addChild($autXML, "CNPJ", $std->CNPJ, false, $identificador . "CNPJ do Cliente Autorizado");
        $this->dom->addChild($autXML, "CPF", $std->CPF, false, $identificador . "CPF do Cliente Autorizado");
        $this->aAutXML[] = $autXML;
        return $autXML;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagInfAdic(stdClass $std)
    {
        $possible = ['infAdFisco', 'infCpl'];
        $std = $this->equilizeParameters($std, $possible);

        $this->infAdic = $this->dom->createElement("infAdic");
        $this->dom->addChild(
            $this->infAdic,
            "infAdFisco",
            $std->infAdFisco,
            false,
            "Informações Adicionais de Interesse do Fisco"
        );
        $this->dom->addChild(
            $this->infAdic,
            "infCpl",
            $std->infCpl,
            false,
            "Informações Complementares de interesse do Contribuinte"
        );
        return $this->infAdic;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagGResptec(stdClass $std)
    {
        $possible = ['CNPJ', 'xContato', 'email', 'fone', 'idCSRT', 'hashCSRT'];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gRespTec> - ';
        $this->gRespTec = $this->dom->createElement("gRespTec");
        $this->dom->addChild(
            $this->gRespTec,
            "CNPJ",
            $std->CNPJ,
            true,
            $identificador . "CNPJ da pessoa jurídica responsável técnica pelo sistema utilizado na emissão do
            documento fiscal"
        );
        $this->dom->addChild(
            $this->gRespTec,
            "xContato",
            $std->xContato,
            true,
            $identificador . "Nome da pessoa a ser contatada"
        );
        $this->dom->addChild(
            $this->gRespTec,
            "email",
            $std->email,
            true,
            $identificador . "Email da pessoa jurídica a ser contatada"
        );
        $this->dom->addChild(
            $this->gRespTec,
            "fone",
            $std->fone,
            true,
            $identificador . "Telefone da pessoa jurídica a ser contatada"
        );
        $this->dom->addChild(
            $this->gRespTec,
            "idCSRT",
            $std->idCSRT,
            true,
            $identificador . "Identificador do código de segurança do responsável técnico"
        );
        $this->dom->addChild(
            $this->gRespTec,
            "hashCSRT",
            $this->hashCSRT($std->hashCSRT),
            true,
            $identificador . "Hash do token do código de segurança do responsável técnico"
        );
        return $this->gRespTec;
    }

    /**
     * @param stdClass $std
     * @return \DOMElement|false
     */
    public function tagNfNFComSupl(stdClass $std)
    {
        $possible = ['chNFCom', 'tpAmb'];
        $std = $this->equilizeParameters($std, $possible);

        $infNFComSupl = $this->dom->createElement("infNFComSupl");
        $nodeqr = $infNFComSupl->appendChild($this->dom->createElement('qrCodNFCom'));
        $nodeqr->appendChild($this->dom->createCDATASection('https://dfe-portal.svrs.rs.gov.br/NFCom/QRCode?chNFCom=' . $std->chNFCom . '&tpAmb=' . $std->tpAmb));
        $this->infNFComSupl = $infNFComSupl;
        return $infNFComSupl;
    }

    /**
     * NFCom xml mount method
     * this function returns TRUE on success or FALSE on error
     * The xml of the NFCom must be retrieved by the getXML() function or
     * directly by the public property $xml
     *
     * @throws RuntimeException
     */
    public function monta()
    {
        if (!empty($this->errors)) {
            $this->errors = array_merge($this->errors, $this->dom->errors);
        } else {
            $this->errors = $this->dom->errors;
        }

        //cria a tag raiz da NFCom
        $this->buildNFCom();

        $this->dom->appChild($this->infNFCom, $this->ide, 'Falta tag "infNFCom"');
        $this->dom->appChild($this->infNFCom, $this->emit, 'Falta tag "infNFCom"');
        $this->dom->appChild($this->infNFCom, $this->dest, 'Falta tag "infNFCom"');
        $this->dom->appChild($this->infNFCom, $this->assinante, 'Falta tag "infNFCom"');
        foreach ($this->aDet as $nItem => $det) {
            $infAdProd = null;
            foreach ($det->childNodes as $node) {
                if ($node->nodeName === 'infAdProd') {
                    $infAdProd = $node;
                    $det->removeChild($node);
                    break;
                }
            }

            if (!empty($this->gSub)) {
                $this->dom->appChild($this->infNFCom, $this->gSub, 'Falta tag "infNFCom"');
            }
            if (!empty($this->gCofat)) {
                $this->dom->appChild($this->infNFCom, $this->gCofat, 'Falta tag "infNFCom"');
            }

            if (!empty($this->aProd[$nItem])) {
                $child = $this->aProd[$nItem];
                $this->dom->appChild($det, $child, "Inclusão do node prod");
            }

            // IMPOSTO
            $imposto = $this->dom->createElement("imposto");
            if (!empty($this->aICMS[$nItem])) {
                $child = $this->aICMS[$nItem];
                $this->dom->appChild($imposto, $child, "Inclusão do node ICMS");
            }
            if (!empty($this->aICMSSN[$nItem])) {
                $child = $this->aICMSSN[$nItem];
                $this->dom->appChild($imposto, $child, "Inclusão do node ICMSSN");
            }
            if (!empty($this->aICMSUFDest[$nItem])) {
                foreach ($this->aICMSUFDest[$nItem] as $aICMSUFDest) {
                    $this->dom->appChild($imposto, $aICMSUFDest, "Inclusão do node aICMSUFDest");
                }
            }
            if (!empty($this->aPIS[$nItem])) {
                $child = $this->aPIS[$nItem];
                $this->dom->appChild($imposto, $child, "Inclusão do node PIS");
            }
            if (!empty($this->aCOFINS[$nItem])) {
                $child = $this->aCOFINS[$nItem];
                $this->dom->appChild($imposto, $child, "Inclusão do node COFINS");
            }
            if (!empty($this->aFUST[$nItem])) {
                $child = $this->aFUST[$nItem];
                $this->dom->appChild($imposto, $child, "Inclusão do node FUST");
            }
            if (!empty($this->aFUNTTEL[$nItem])) {
                $child = $this->aFUNTTEL[$nItem];
                $this->dom->appChild($imposto, $child, "Inclusão do node FUNTTEL");
            }
            if (!empty($this->aRetTrib[$nItem])) {
                $child = $this->aRetTrib[$nItem];
                $this->dom->appChild($imposto, $child, "Inclusão do node RetTrib");
            }
            if (!empty($this->aIBSCBS[$nItem])) {
                $IBSCBS = $this->aIBSCBS[$nItem];

                if (!empty($this->aGIBSCBS[$nItem])) {
                    $aGIBSCBS = $this->aGIBSCBS[$nItem];
                    $this->dom->appChild($IBSCBS, $aGIBSCBS, "Inclusão do node gIBSCBS");

                    if (!empty($this->aGIBSUF[$nItem])) {
                        $child = $this->aGIBSUF[$nItem];
                        $this->dom->appChild($aGIBSCBS, $child, "Inclusão do node gIBSUF");
                    }

                    if (!empty($this->aGIBSMun[$nItem])) {
                        $child = $this->aGIBSMun[$nItem];
                        $this->dom->appChild($aGIBSCBS, $child, "Inclusão do node gIBSMun");
                    }

                    if (!empty($this->aGCBS[$nItem])) {
                        $child = $this->aGCBS[$nItem];
                        $this->dom->appChild($aGIBSCBS, $child, "Inclusão do node gCBS");
                    }

                    if (!empty($this->aGTribRegular[$nItem])) {
                        $child = $this->aGTribRegular[$nItem];
                        $this->dom->appChild($aGIBSCBS, $child, "Inclusão do node gTribRegular");
                    }

                    if (!empty($this->aGIBSCredPres[$nItem])) {
                        $child = $this->aGIBSCredPres[$nItem];
                        $this->dom->appChild($aGIBSCBS, $child, "Inclusão do node gIBSCredPres");
                    }

                    if (!empty($this->aGCBSCredPres[$nItem])) {
                        $child = $this->aGCBSCredPres[$nItem];
                        $this->dom->appChild($aGIBSCBS, $child, "Inclusão do node gCBSCredPres");
                    }

                    if (!empty($this->aGTribCompraGov[$nItem])) {
                        $child = $this->aGTribCompraGov[$nItem];
                        $this->dom->appChild($aGIBSCBS, $child, "Inclusão do node gTribCompraGov");
                    }
                }

                $this->dom->appChild($imposto, $IBSCBS, "Inclusão do node IBSCBS");
            }

            $this->dom->appChild($det, $imposto, "Inclusão do node imposto");

            // GPROCREF
            if (!empty($this->aGProcRef[$nItem])) {
                $gProcRef = $this->aGProcRef[$nItem];
                if (!empty($this->aGProc[$nItem])) {
                    $child = $this->aGProc[$nItem];
                    $this->dom->appChild($gProcRef, $child, "Inclusão do node gProc");
                }
                if (!empty($this->aGRessarc[$nItem])) {
                    $child = $this->aGRessarc[$nItem];
                    $this->dom->appChild($gProcRef, $child, "Inclusão do node gRessarc");
                }
                $this->dom->appChild($det, $gProcRef, "Inclusão do node gProcRef");
            }

            if ($infAdProd !== null) {
                $this->dom->appChild($det, $infAdProd, "Inclusão tag infAdProd");
            }

            $this->dom->appChild($this->infNFCom, $det, 'Falta tag "infNFCom"');
        }
        $this->dom->appChild($this->infNFCom, $this->total, 'Falta tag "infNFCom"');
        if (!empty($this->gFidelidade)) {
            $this->dom->appChild($this->infNFCom, $this->gFidelidade, 'Falta tag "infNFCom"');
        }
        if (!empty($this->gFat)) {
            $this->dom->appChild($this->infNFCom, $this->gFat, 'Falta tag "infNFCom"');
        }
        if (!empty($this->gFatCentral)) {
            $this->dom->appChild($this->infNFCom, $this->gFatCentral, 'Falta tag "infNFCom"');
        }
        if (count($this->aAutXML) > 0) {
            foreach ($this->aAutXML as $aut) {
                $this->dom->appChild($this->infNFCom, $aut, 'Falta tag "infNFCom"');
            }
        }

        if (!empty($this->infAdic)) {
            $this->dom->appChild($this->infNFCom, $this->infAdic, 'Falta tag "infNFCom"');
        }
        if (!empty($this->gRespTec)) {
            $this->dom->appChild($this->infNFCom, $this->gRespTec, 'Falta tag "infNFCom"');
        }

        $this->dom->appChild($this->NFCom, $this->infNFCom, 'Falta tag "NFCom"');

        $this->dom->appendChild($this->NFCom);

        $this->checkNFComKey($this->dom);

        $std = new \stdClass();
        $std->chNFCom = $this->chNFCom;
        $std->tpAmb = $this->ide->getElementsByTagName('tpAmb')->item(0)->nodeValue;
        $this->infNFComSupl = $this->tagNfNFComSupl($std);

        $this->dom->appChild($this->NFCom, $this->infNFComSupl, 'Falta tag "NFCom"');

        $this->xml = $this->dom->saveXML();
        if (count($this->errors) > 0) {
            throw new RuntimeException('Existem erros nas tags. Obtenha os erros com getErrors().');
        }
        return $this->xml;
    }

    /**
     * Tag raiz da NFCom
     * tag NFCom DOMNode
     * Função chamada pelo método [ monta ]
     *
     */
    protected function buildNFCom()
    {
        if (empty($this->NFCom)) {
            $this->NFCom = $this->dom->createElement("NFCom");
            $this->NFCom->setAttribute("xmlns", "http://www.portalfiscal.inf.br/nfcom");
        }
        return $this->NFCom;
    }

    /**
     * Includes missing or unsupported properties in stdClass
     * Replace all unsuported chars
     */
    protected function equilizeParameters(stdClass $std, $possible)
    {
        return Strings::equilizeParameters($std, $possible, $this->replaceAccentedChars);
    }

    /**
     * Calcula hash sha1 retornando Base64Binary
     */
    protected function hashCSRT(string $CSRT): string
    {
        $comb = $CSRT . $this->chNFe;
        return base64_encode(sha1($comb, true));
    }

    /**
     * Formatação numerica condicional
     * @param string|float|int|null $value
     */
    protected function conditionalNumberFormatting($value = null, $decimal = 2)
    {
        if (is_numeric($value)) {
            return number_format($value, $decimal, '.', '');
        }
        return null;
    }

    /**
     * Remonta a chave da NFCom de 44 digitos com base em seus dados
     * já contidos na NFCom.
     * Isso é útil no caso da chave informada estar errada
     * se a chave estiver errada a mesma é substituida
     */
    protected function checkNFComKey(Dom $dom): void
    {
        $infNFCom = $dom->getElementsByTagName("infNFCom")->item(0);
        $ide = $dom->getElementsByTagName("ide")->item(0);
        $emit = $dom->getElementsByTagName("emit")->item(0);
        $cUF = $ide->getElementsByTagName('cUF')->item(0)->nodeValue;
        $dhEmi = $ide->getElementsByTagName('dhEmi')->item(0)->nodeValue;
        if (!empty($emit->getElementsByTagName('CNPJ')->item(0)->nodeValue)) {
            $doc = $emit->getElementsByTagName('CNPJ')->item(0)->nodeValue;
        } else {
            $doc = $emit->getElementsByTagName('CPF')->item(0)->nodeValue;
        }
        $mod = $ide->getElementsByTagName('mod')->item(0)->nodeValue;
        $serie = $ide->getElementsByTagName('serie')->item(0)->nodeValue;
        $nNF = $ide->getElementsByTagName('nNF')->item(0)->nodeValue;
        $tpEmis = $ide->getElementsByTagName('tpEmis')->item(0)->nodeValue;
        $cNF = $ide->getElementsByTagName('cNF')->item(0)->nodeValue;
        $chave = str_replace('NFCom', '', $infNFCom->getAttribute("Id"));
        $dt = new DateTime($dhEmi);
        $infRespTec = $dom->getElementsByTagName("gRespTec")->item(0);
        $chaveMontada = Keys::build(
            $cUF,
            $dt->format('y'),
            $dt->format('m'),
            $doc,
            $mod,
            $serie,
            $nNF,
            $tpEmis,
            $cNF
        );
        if (empty($chave)) {
            $infNFCom->setAttribute('Id', "NFCom$chaveMontada");
            $chave = $chaveMontada;
            $this->chNFCom = $chaveMontada;
            $ide->getElementsByTagName('cDV')->item(0)->nodeValue = substr($chave, -1);
            if (!empty($this->csrt)) {
                $hashCSRT = $this->hashCSRT($this->csrt);
                $infRespTec->getElementsByTagName("hashCSRT")
                    ->item(0)->nodeValue = $hashCSRT;
            }
        }
        //caso a chave contida na NFe esteja errada
        //substituir a chave
        if ($chaveMontada != $chave) {
            $this->chNFCom = $chaveMontada;
            $this->errors[] = "A chave informada está incorreta [$chave] => [correto: $chaveMontada].";
        }
    }

    /**
     * Retorna os erros detectados
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Formatação numerica condicional
     * @param string|float|int|null $value
     */
    public function getXML()
    {
        if (empty($this->xml)) {
            $this->monta();
        }
        return $this->xml;
    }

    /**
     * Retorns the key number of NFe (44 digits)
     */
    public function getChave(): string
    {
        return $this->chNFCom;
    }
}
