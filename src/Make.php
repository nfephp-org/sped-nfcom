<?php

namespace NFePHP\NFCom;

use NFePHP\Common\Keys;
use NFePHP\Common\DOMImproved as Dom;
use NFePHP\Common\Strings;
use RuntimeException;
use InvalidArgumentException;
use stdClass;

class Make
{

    public $errors = [];
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
    protected $aGProcRef = [];
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
     * @return DOMElement
     */
    public function tagInfNFCom(stdClass $std)
    {
        $possible = ['Id', 'versao'];
        $std = $this->equilizeParameters($std, $possible);

        $this->infNFCom = $this->dom->createElement("infNFCom");
        $this->infNFCom->setAttribute("Id", 'NFCom' . $std->Id);
        $this->infNFCom->setAttribute("versao", $std->versao);
        return $this->infNFCom;
    }

    /**
     * @param stdClass $std
     * @return DOMElement
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

        // if (empty($std->cNF)) {
        //     $std->cNF = Keys::random($std->nNF);
        // }
        // if (empty($std->cDV)) {
        //     $std->cDV = 0;
        // }
        // //validação conforme NT 2019.001
        // $std->cNF = str_pad($std->cNF, 8, '0', STR_PAD_LEFT);
        // if (intval($std->cNF) == intval($std->nNF)) {
        //     throw new InvalidArgumentException("O valor [{$std->cNF}] não é " . "aceitável para cNF, não pode ser igual ao de nNF, vide NT2019.001");
        // }
        // if (method_exists(Keys::class, 'cNFIsValid')) {
        //     if (!Keys::cNFIsValid($std->cNF)) {
        //         throw new InvalidArgumentException("O valor [{$std->cNF}] para cNF " . "é invalido, deve respeitar a NT2019.001");
        //     }
        // }

        $identificador = '<ide> - ';

        $ide = $this->dom->createElement("ide");
        $this->dom->addChild($ide, "cUF", $std->cUF, true, $identificador . "Código da UF do emitente do Documento Fiscal");
        $this->dom->addChild($ide, "tpAmb", $std->tpAmb, true, $identificador . "Identificação do Ambiente");
        $this->dom->addChild($ide, "mod", $std->mod, true, $identificador . "Código do Modelo do Documento Fiscal");
        $this->dom->addChild($ide, "serie", $std->serie, true, $identificador . "Série do Documento Fiscal");
        $this->dom->addChild($ide, "nNF", $std->nNF, true, $identificador . "Número do Documento Fiscal");
        $this->dom->addChild($ide, "cNF", $std->cNF, true, $identificador . "Código Numérico que compõe a Chave de Acesso");
        $this->dom->addChild($ide, "cDV", !empty($std->cDV) ? $std->cDV : '0', true, $identificador . "Dígito Verificador da Chave de Acesso");
        $this->dom->addChild($ide, "dhEmi", $std->dhEmi, true, $identificador . "Data e hora de emissão do Documento Fiscal");
        $this->dom->addChild($ide, "tpEmis", $std->tpEmis, true, $identificador . "Tipo de Emissão da Documento Fiscal");
        $this->dom->addChild($ide, "nSiteAutoriz", $std->nSiteAutoriz, true, $identificador . "Identificação do número do Site do Autorizador de recepção da NFCom");
        $this->dom->addChild($ide, "cMunFG", $std->cMunFG, true, $identificador . "Código do Município de Ocorrência do Fato Gerador");
        $this->dom->addChild($ide, "finNFCom", $std->finNFCom, true, $identificador . "Finalidade de emissão da NFCom");
        $this->dom->addChild($ide, "tpFat", $std->tpFat, true, $identificador . "Tipo de Faturamento da NFCom");
        $this->dom->addChild($ide, "verProc", $std->verProc, true, $identificador . "Versão do Processo de emissão");
        $this->dom->addChild($ide, "indPrePago", $std->indPrePago, true, $identificador . "Indicador de serviço pré-pago");
        $this->dom->addChild($ide, "indCessaoMeiosRede", $std->indCessaoMeiosRede, true, $identificador . "Indicador de Sessão de Meios de Rede");
        if (!empty($std->dhCont) && !empty($std->xJust)) {
            $this->dom->addChild($ide, "dhCont", $std->dhCont, true, $identificador . "Data e Hora da entrada em contingência");
            $this->dom->addChild($ide, "xJust", substr(trim($std->xJust), 0, 256), true, $identificador . "Justificativa da entrada em contingência");
        }

        $this->ide = $ide;
        return $ide;
    }

    /**
     * @param stdClass $std
     * @return DOMElement
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
        $this->dom->addChild($this->emit, "CNPJ", Strings::onlyNumbers($std->CNPJ), false, $identificador . "CNPJ do emitente");
        if ($std->IE != 'ISENTO') {
            $std->IE = Strings::onlyNumbers($std->IE);
        }
        $this->dom->addChild($this->emit, "IE", $std->IE, true, $identificador . "Inscrição Estadual do emitente");
        $this->dom->addChild($this->emit, "IEUFDest", Strings::onlyNumbers($std->IEUFDest), false, $identificador . "Inscrição Estadual Virtual do emitente na UF de Destino da partilha (IE Virtual)");
        $this->dom->addChild($this->emit, "CRT", $std->CRT, true, $identificador . "Código de Regime Tributário do emitente");
        $this->dom->addChild($this->emit, "xNome", substr(trim($std->xNome), 0, 60), true, $identificador . "Razão Social ou Nome do emitente");
        if(!empty($std->xFant)){
            $this->dom->addChild($this->emit, "xFant", substr(trim($std->xFant), 0, 60), false, $identificador . "Nome fantasia do emitente");
        }
        return $this->emit;
    }

    /**
     * @param stdClass $std
     * @return DOMElement
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
            'UF',
            'CEP',
            'fone',
            'email'
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<enderEmit> - ';
        $this->enderEmit = $this->dom->createElement("enderEmit");
        $this->dom->addChild($this->enderEmit, "xLgr", substr(trim($std->xLgr), 0, 60), true, $identificador . "Logradouro do Endereço do emitente");
        $this->dom->addChild($this->enderEmit, "nro", substr(trim($std->nro), 0, 60), true, $identificador . "Número do Endereço do emitente");
        $this->dom->addChild($this->enderEmit, "xCpl", substr(trim($std->xCpl), 0, 60), false, $identificador . "Complemento do Endereço do emitente");
        $this->dom->addChild($this->enderEmit, "xBairro", substr(trim($std->xBairro), 0, 60), true, $identificador . "Bairro do Endereço do emitente");
        $this->dom->addChild($this->enderEmit, "cMun", Strings::onlyNumbers($std->cMun), true, $identificador . "Código do município do Endereço do emitente");
        $this->dom->addChild($this->enderEmit, "xMun", substr(trim($std->xMun), 0, 60), true, $identificador . "Nome do município do Endereço do emitente");
        $this->dom->addChild($this->enderEmit, "UF", strtoupper(trim($std->UF)), true, $identificador . "Sigla da UF do Endereço do emitente");
        $this->dom->addChild($this->enderEmit, "CEP", Strings::onlyNumbers($std->CEP), true, $identificador . "Código do CEP do Endereço do emitente");
        if(!empty($std->fone)){
            $this->dom->addChild($this->enderEmit, "fone", trim($std->fone), false, $identificador . "Telefone do Endereço do emitente");
        }
        if(!empty($std->email)){
            $this->dom->addChild($this->enderEmit, "email", trim($std->email), false, $identificador . "Endereço de E-mail do emitente");
        }
        $node = $this->emit->getElementsByTagName("IE")->item(0);
        $this->emit->insertBefore($this->enderEmit, $node);
        return $this->enderEmit;
    }

    /**
     * @param stdClass $std
     * @return DOMElement
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

        if($std->indIEDest===2){
            $std->IE = "ISENTO";
        }

        $this->dest = $this->dom->createElement("dest");
        $this->dom->addChild($this->emit, "xNome", substr(trim($std->xNome), 0, 60), true, $identificador . "Razão Social ou Nome do destinatário");
        if (!empty($std->CNPJ)) {
            $this->dom->addChild($this->dest, "CNPJ", Strings::onlyNumbers($std->CNPJ), true, $identificador . "CNPJ do destinatário");
        } elseif (!empty($std->CPF)) {
            $this->dom->addChild($this->dest, "CPF", Strings::onlyNumbers($std->CPF), true, $identificador . "CPF do destinatário");
        } elseif ($std->idOutros !== null) {
            $this->dom->addChild($this->dest, "idOutros", $std->idOutros, true, $identificador . "Identificação do destinatário no caso de comprador estrangeiro");
        }
        $this->dom->addChild($this->dest, "indIEDest", Strings::onlyNumbers($std->indIEDest), true, $identificador . "Indicador da IE do Destinatário");
        if (!empty($std->IE)) {
            $this->dom->addChild($this->dest, "IE", $std->IE, true, $identificador . "Inscrição Estadual do Destinatário");
        }
        if(!empty($std->IM)){
            $this->dom->addChild($this->dest, "IM", Strings::onlyNumbers($std->IM), false, $identificador . "Inscrição Municipal do destinatário");
        }
        return $this->dest;
    }

    /**
     * @param stdClass $std
     * @return DOMElement
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
        $this->dom->addChild($this->enderDest, "xLgr", $std->xLgr, true, $identificador . "Logradouro do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "nro", $std->nro, true, $identificador . "Número do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "xCpl", $std->xCpl, false, $identificador . "Complemento do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "xBairro", $std->xBairro, true, $identificador . "Bairro do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "cMun", $std->cMun, true, $identificador . "Código do município do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "xMun", $std->xMun, true, $identificador . "Nome do município do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "UF", $std->UF, true, $identificador . "Sigla da UF do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "CEP", $std->CEP, false, $identificador . "Código do CEP do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "cPais", $std->cPais, false, $identificador . "Código do País do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "xPais", $std->xPais, false, $identificador . "Nome do País do Endereço do Destinatário");
        $this->dom->addChild($this->enderDest, "fone", $std->fone, false, $identificador . "Telefone do Endereço do Destinatário");
        $node = $this->dest->getElementsByTagName("indIEDest")->item(0);
        if (!isset($node)) {
            $node = $this->dest->getElementsByTagName("IE")->item(0);
            if (!isset($node)) {
                $node = $this->dest->getElementsByTagName("IM")->item(0);
            }
        }
        $this->dest->insertBefore($this->enderDest, $node);
        return $this->enderDest;
    }

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
        $this->dom->addChild($this->assinante, "iCodAssinante", $std->iCodAssinante, true, $identificador . "Código único de Identificação do assinante");
        $this->dom->addChild($this->assinante, "tpAssinante", $std->tpAssinante, true, $identificador . "Tipo de assinante");
        $this->dom->addChild($this->assinante, "tpServUtil", $std->tpServUtil, true, $identificador . "Tipo de serviço utilizado");
        $this->dom->addChild($this->assinante, "nContrato", $std->nContrato, true, $identificador . "Número do Contrato do assinante");
        $this->dom->addChild($this->assinante, "dContratoIni", $std->dContratoIni, true, $identificador . "Data de início do contrato");
        $this->dom->addChild($this->assinante, "dContratoFim", $std->dContratoFim, true, $identificador . "Data de término do contrato");
        if(!empty($std->NroTermPrinc) && !empty($std->cUFPrinc)){
            $this->dom->addChild($this->assinante, "NroTermPrinc", $std->NroTermPrinc, true, $identificador . "Número do Terminal Principal do serviço contratado");
            $this->dom->addChild($this->assinante, "cUFPrinc", $std->cUFPrinc, true, $identificador . "Código da UF de habilitação do terminal");
        }
        if(!empty($std->NroTermAdic) && !empty($std->cUFAdic)){
            $this->dom->addChild($this->assinante, "NroTermAdic", $std->NroTermAdic, true, $identificador . "Número dos Terminais adicionais do serviço contratado");
            $this->dom->addChild($this->assinante, "cUFAdic", $std->cUFAdic, true, $identificador . "Código da UF de habilitação do terminal");
        }
        return $this->assinante;
    }

    public function tagGSub(stdClass $std)
    {
        $possible = [
            'chNFCom',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gSub> - ';
        $this->gSub = $this->dom->createElement("gSub");
        $this->dom->addChild($this->gSub, "chNFCom", $std->chNFCom, true, $identificador . "Chave de acesso da NFCom original");
        return $this->gSub;
    }

    /**
     * @param stdClass $std
     * @return DOMElement
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
        $this->dom->addChild($this->gNF, "CompetEmis", $std->CompetEmis, true, $identificador . "Ano e mês da emissão da NF");
        if($std->hash115){
            $this->dom->addChild($this->gNF, "hash115", $std->hash115, true, $identificador . "Hash do registro no arquivo do convênio 115");
        }
        $this->dom->addChild($this->gNF, "motSub", $std->motSub, true, $identificador . "Motivo da substituição");

        $node = $this->dest->getElementsByTagName("chNFCom")->item(0);
        $this->gSub->insertBefore($this->gNF, $node);
        return $this->gNF;
    }

    public function tagGCofat(stdClass $std)
    {
        $possible = [
            'chNFComLocal',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gCofat> - ';
        $this->gCofat = $this->dom->createElement("gCofat");
        $this->dom->addChild($this->gCofat, "chNFComLocal", $std->chNFComLocal, true, $identificador . "Chave de acesso da NFCom emitida pela Operadora Local");
        return $this->gCofat;
    }

    public function tagDet(stdClass $std)
    {
        $possible = [
            'item',
            'nItem',
            'chNFComAnt',
            'nItemAnt'
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<det> - ';
        $det = $this->dom->createElement("det");
        $this->dom->addChild($det, "nItem", $std->nItem, true, $identificador . "[item $std->item] Número do item da NFCom");
        $this->dom->addChild($det, "chNFComAnt", $std->chNFComAnt, true, $identificador . "[item $std->item] Chave de Acesso da NFCom anterior");
        $this->dom->addChild($det, "nItemAnt", $std->nItemAnt, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->aDet[$std->item] = $det;
        return $det;
    }

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
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<prod> - ';
        $prod = $this->dom->createElement("prod");
        $this->dom->addChild($prod, "cProd", $std->cProd, true, $identificador . "[item $std->item] Número do item da NFCom");
        $this->dom->addChild($prod, "xProd", $std->xProd, true, $identificador . "[item $std->item] Chave de Acesso da NFCom anterior");
        $this->dom->addChild($prod, "cClass", $std->cClass, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "CFOP", $std->CFOP, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "CNPJLD", $std->CNPJLD, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "uMed", $std->uMed, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "qFaturada", $std->qFaturada, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "vItem", $std->vItem, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "vDesc", $std->vDesc, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "vOutro", $std->vOutro, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "vProd", $std->vProd, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "dExpiracao", $std->dExpiracao, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->dom->addChild($prod, "indDevolucao", $std->indDevolucao, false, $identificador . "[item $std->item] Número do item da NFCom anterior");
        $this->aProd[$std->item] = $prod;
        return $prod;
    }

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
                $this->dom->addChild($icms,'CST', $std->CST, true, "$identificador [item $std->item] Tributação do ICMS = 00");
                $this->dom->addChild($icms,'vBC', $this->conditionalNumberFormatting($std->vBC), true, "$identificador [item $std->item] Valor da BC do ICMS");
                $this->dom->addChild($icms,'pICMS', $this->conditionalNumberFormatting($std->pICMS, 4), true, "$identificador [item $std->item] Alíquota do imposto");
                $this->dom->addChild($icms,'vICMS', $this->conditionalNumberFormatting($std->vICMS), true, "$identificador [item $std->item] Valor do ICMS");
                $this->dom->addChild($icms,'pFCP', $this->conditionalNumberFormatting($std->pFCP, 4), false, "$identificador [item $std->item] Percentual do Fundo de ". "Combate à Pobreza (FCP)");
                $this->dom->addChild($icms,'vFCP', $this->conditionalNumberFormatting($std->vFCP), false, "$identificador [item $std->item] Valor do Fundo de Combate ". "à Pobreza (FCP)");
                break;

            case '20':
                $icms = $this->dom->createElement("ICMS20");
                $this->dom->addChild($icms,'CST', $std->CST, true, "$identificador [item $std->item] Tributação do ICMS = 20");
                $this->dom->addChild($icms,'pRedBC', $this->conditionalNumberFormatting($std->pRedBC, 4), true, "$identificador [item $std->item] Percentual da Redução de BC");
                $this->dom->addChild($icms,'vBC', $this->conditionalNumberFormatting($std->vBC), true, "$identificador [item $std->item] Valor da BC do ICMS");
                $this->dom->addChild($icms,'pICMS', $this->conditionalNumberFormatting($std->pICMS, 4), true, "$identificador [item $std->item] Alíquota do imposto");
                $this->dom->addChild($icms,'vICMS', $this->conditionalNumberFormatting($std->vICMS), true, "$identificador [item $std->item] Valor do ICMS");
                $this->dom->addChild($icms,'vICMSDeson', $this->conditionalNumberFormatting($std->vICMSDeson), false, "$identificador [item $std->item] Valor do ICMS desonerado");
                $this->dom->addChild($icms,'cBenef', $std->cBenef, false, "$identificador [item $std->item] Código de Benefício Fiscal na UF aplicado ao item");
                $this->dom->addChild($icms,'pFCP', $this->conditionalNumberFormatting($std->pFCP, 4), false, "$identificador [item $std->item] Percentual do Fundo de ". "Combate à Pobreza (FCP)");
                $this->dom->addChild($icms,'vFCP', $this->conditionalNumberFormatting($std->vFCP), false, "$identificador [item $std->item] Valor do FCP");
                break;

            case '40':
                $icms = $this->dom->createElement("ICMS40");
                $this->dom->addChild($icms,'CST', $std->CST, true, "$identificador [item $std->item] Tributação do ICMS $std->CST");
                $this->dom->addChild($icms,'vICMSDeson', $this->conditionalNumberFormatting($std->vICMSDeson), false, "$identificador [item $std->item] Valor do ICMS desonerado");
                $this->dom->addChild($icms,'cBenef', $std->cBenef, false, "$identificador [item $std->item] Código de Benefício Fiscal na UF aplicado ao item");
                break;

            case '51':
                $icms = $this->dom->createElement("ICMS51");

                $this->dom->addChild($icms,'CST', $std->CST, true, "$identificador [item $std->item] Tributação do ICMS $std->CST");
                $this->dom->addChild($icms,'vICMSDeson', $this->conditionalNumberFormatting($std->vICMSDeson), false, "$identificador [item $std->item] Valor do ICMS desonerado");
                $this->dom->addChild($icms,'cBenef', $std->cBenef, false, "$identificador [item $std->item] Código de Benefício Fiscal na UF aplicado ao item");
                break;

            case '90':
                $icms = $this->dom->createElement("ICMS90");
                $this->dom->addChild($icms,'CST', $std->CST, true, "$identificador [item $std->item] Tributação do ICMS = 90");
                $this->dom->addChild($icms,'vBC', $this->conditionalNumberFormatting($std->vBC), false, "$identificador [item $std->item] Valor da BC do ICMS");
                $this->dom->addChild($icms,'pICMS', $this->conditionalNumberFormatting($std->pICMS, 4), false, "$identificador [item $std->item] Alíquota do imposto");
                $this->dom->addChild($icms,'vICMS', $this->conditionalNumberFormatting($std->vICMS), false, "$identificador [item $std->item] Valor do ICMS");
                break;
        }
        $this->aICMS[$std->item] = $icms;
        return $icms;
    }

    public function tagICMSSN(stdClass $std)
    {
        $possible = [
            'item',
            'CST',
            'indSN',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $icmsSN = $this->dom->createElement("ICMSSN");
        $this->dom->addChild($icmsSN, 'CST', $std->orig, true,"[item $std->item] Classificação Tributária do Serviço");
        $this->dom->addChild($icmsSN, 'indSN', $std->CSOSN, true,"[item $std->item] Indica se o contribuinte é Simples Nacional 1=Sim");
        $this->aICMSSN[$std->item] = $icmsSN;
        return $icmsSN;
    }

    public function tagICMSUFDest(stdClass $std)
    {
        $possible = [
            'item',
            'cUFDest',
            'vBCUFDest',
            'pFCPUFDest',
            'pICMSUFDest',
            'pICMSInter',
            'vFCPUFDest',
            'vICMSUFDest',
            'vICMSUFEmi',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $ICMSUFDest = $this->dom->createElement("ICMSUFDest");
        $this->dom->addChild($ICMSUFDest, 'cUFDest', $std->cUFDest, true,"[item $std->item] Classificação Tributária do Serviço");
        $this->dom->addChild($ICMSUFDest, 'vBCUFDest', $std->vBCUFDest, true,"[item $std->item] Valor da BC do ICMS na UF de destino ");
        $this->dom->addChild($ICMSUFDest, 'pFCPUFDest', $std->pFCPUFDest, true,"[item $std->item] Percentual do ICMS relativo ao Fundo de Combate à pobreza (FCP) na UF de destino");
        $this->dom->addChild($ICMSUFDest, 'pICMSUFDest', $std->pICMSUFDest, true,"[item $std->item] Alíquota interna da UF de destino");
        $this->dom->addChild($ICMSUFDest, 'pICMSInter', $std->pICMSInter, true,"[item $std->item] Alíquota interestadual das UF envolvidas");
        $this->dom->addChild($ICMSUFDest, 'vFCPUFDest', $std->vFCPUFDest, true,"[item $std->item] Valor do ICMS relativo ao Fundo de Combate á Pobreza (FCP) da UF de destino");
        $this->dom->addChild($ICMSUFDest, 'vICMSUFDest', $std->vICMSUFDest, true,"[item $std->item] Valor do ICMS de partilha para a UF de destino");
        $this->dom->addChild($ICMSUFDest, 'vICMSUFEmi', $std->vICMSUFEmi, true,"[item $std->item] Valor do ICMS de partilha para a UF de emissão");
        $this->aICMSUFDest[$std->item][] = $ICMSUFDest;
        return $ICMSUFDest;
    }

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
        $this->dom->addChild($PIS, 'CST', $std->cUFDest, true,"[item $std->item] classificação Tributária do PIS");
        $this->dom->addChild($PIS, 'vBC', $std->vBCUFDest, true,"[item $std->item] Valor da BC do PIS");
        $this->dom->addChild($PIS, 'pPIS', $std->pFCPUFDest, true,"[item $std->item] Alíquota do PIS (em percentual) ");
        $this->dom->addChild($PIS, 'vPIS', $std->pPIS, true,"[item $std->item] Valor do PIS");
        $this->aPIS[$std->item] = $PIS;
        return $PIS;
    }

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
        $this->dom->addChild($COFINS, 'CST', $std->cUFDest, true,"[item $std->item] classificação Tributária do COFINS");
        $this->dom->addChild($COFINS, 'vBC', $std->vBCUFDest, true,"[item $std->item] Valor da BC do COFINS");
        $this->dom->addChild($COFINS, 'pCOFINS', $std->pFCPUFDest, true,"[item $std->item] Alíquota do COFINS (em percentual) ");
        $this->dom->addChild($COFINS, 'vCOFINS', $std->pCOFINS, true,"[item $std->item] Valor do COFINS");
        $this->aCOFINS[$std->item] = $COFINS;
        return $COFINS;
    }

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
        $this->dom->addChild($FUST, 'vBC', $std->vBCUFDest, true,"[item $std->item] Valor da BC do FUST");
        $this->dom->addChild($FUST, 'pFUST', $std->pFCPUFDest, true,"[item $std->item] Alíquota do FUST (em percentual) ");
        $this->dom->addChild($FUST, 'vFUST', $std->pFUST, true,"[item $std->item] Valor do FUST");
        $this->aFUST[$std->item] = $FUST;
        return $FUST;
    }

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
        $this->dom->addChild($FUNTTEL, 'vBC', $std->vBCUFDest, true,"[item $std->item] Valor da BC do FUNTTEL");
        $this->dom->addChild($FUNTTEL, 'pFUNTTEL', $std->pFCPUFDest, true,"[item $std->item] Alíquota do FUNTTEL (em percentual) ");
        $this->dom->addChild($FUNTTEL, 'vFUNTTEL', $std->pFUNTTEL, true,"[item $std->item] Valor do FUNTTEL");
        $this->aFUNTTEL[$std->item] = $FUNTTEL;
        return $FUNTTEL;
    }

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
        $this->dom->addChild($retTrib, 'vRetPIS', $std->vRetPIS, true,"[item $std->item] Valor do PIS retido");
        $this->dom->addChild($retTrib, 'vRetCofins', $std->vRetCofins, true,"[item $std->item] Valor do COFNS retido ");
        $this->dom->addChild($retTrib, 'vRetCSLL', $std->vRetCSLL, true,"[item $std->item] Valor da CSLL retida");
        $this->dom->addChild($retTrib, 'vBCIRRF', $std->vBCIRRF, true,"[item $std->item] Base de cálculo do IRRF");
        $this->dom->addChild($retTrib, 'vIRRF', $std->vIRRF, true,"[item $std->item] Valor do IRRF retido");
        $this->aRetTrib[$std->item] = $retTrib;
        return $retTrib;
    }

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
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gProcRef> - ';
        $gProcRef = $this->dom->createElement("gProcRef");
        $this->dom->addChild($gProcRef, "vItem", $std->vItem, true, $identificador . "[item $std->item] Valor unitário do item ");
        $this->dom->addChild($gProcRef, "qFaturada", $std->qFaturada, true, $identificador . "[item $std->item] Quantidade Faturada ");
        $this->dom->addChild($gProcRef, "vProd", $std->vProd, false, $identificador . "[item $std->item] Valor total do item ");
        $this->dom->addChild($gProcRef, "vDesc", $std->vDesc, false, $identificador . "[item $std->item] Valor do Desconto");
        $this->dom->addChild($gProcRef, "vOutro", $std->vOutro, false, $identificador . "[item $std->item] Outras despesas acessórias ");
        $this->dom->addChild($gProcRef, "indDevolucao", $std->indDevolucao, false, $identificador . "[item $std->item] Indicador de devolução do valor do item");
        $this->dom->addChild($gProcRef, "vBC", $std->vBC, false, $identificador . "[item $std->item] Valor da BC do ICMS");
        $this->dom->addChild($gProcRef, "pICMS", $std->pICMS, false, $identificador . "[item $std->item] Alíquota do ICMS ");
        $this->dom->addChild($gProcRef, "vICMS", $std->vICMS, false, $identificador . "[item $std->item] Valor do ICMS");
        $this->dom->addChild($gProcRef, "vPIS", $std->vPIS, false, $identificador . "[item $std->item] Valor do PIS");
        $this->dom->addChild($gProcRef, "vCOFINS", $std->vCOFINS, false, $identificador . "[item $std->item] Valor do COFINS");
        $this->aGProcRef[$std->item] = $gProcRef;
        return $gProcRef;
    }

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
        $this->dom->addChild($gProc, "tpProc", $std->tpProc, true, $identificador . "[item $std->item] Tipo de Processo");
        $this->dom->addChild($gProc, "nProcesso", $std->nProcesso, true, $identificador . "[item $std->item] Número do Processo");
        $this->aGProc[$std->item] = $gProc;
        return $gProc;
    }

    public function tagGRessarc(stdClass $std)
    {
        $possible = [
            'item',
            'tpRessarc',
            'dRef',
            'nProcesso',
            'nProtReclama',
            'xObs',
            'infAdProd',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gRessarc> - ';
        $gRessarc = $this->dom->createElement("gRessarc");
        $this->dom->addChild($gRessarc, "tpRessarc", $std->tpRessarc, true, $identificador . "[item $std->item] Tipo de Ressarcimento");
        $this->dom->addChild($gRessarc, "dRef", $std->dRef, true, $identificador . "[item $std->item] Data de referência");
        $this->dom->addChild($gRessarc, "nProcesso", $std->nProcesso, true, $identificador . "[item $std->item] Número do Processo");
        $this->dom->addChild($gRessarc, "nProtReclama", $std->nProtReclama, true, $identificador . "[item $std->item] Número do protocolo de reclamação");
        $this->dom->addChild($gRessarc, "xObs", $std->xObs, true, $identificador . "[item $std->item] Observações sobre o processo de ressarcimento");
        $this->dom->addChild($gRessarc, "infAdProd", $std->infAdProd, true, $identificador . "[item $std->item] Informações adicionais do produto (norma referenciada, informações complementares, etc)");
        $this->aGRessarc[$std->item] = $gRessarc;
        return $gRessarc;
    }

    public function tagTotal(stdClass $std)
    {
        $possible = [
            'vProd',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<total> - ';
        $this->total = $this->dom->createElement("total");
        $this->dom->addChild($this->total, "vProd", $std->vProd, true, $identificador . "Valor Total dos produtos e serviços");
        return $this->total;
    }

    public function tagICMSTot(stdClass $std)
    {
        $possible = [
            'vBC',
            'vICMS',
            'vICMSDeson',
            'vFCP',
            'vCOFINS',
            'vPIS',
            'vFUNTTEL',
            'vFUST',
        ];
        if (isset($std)) {
            $std = $this->equilizeParameters($std, $possible);
        }

        $ICMSTot = $this->dom->createElement("ICMSTot");
        $this->dom->addChild($ICMSTot, "vBC",$this->conditionalNumberFormatting($std->vBC), true, "Base de Cálculo do ICMS");
        $this->dom->addChild($ICMSTot, "vICMS",$this->conditionalNumberFormatting($std->vICMS), true, "Valor Total do ICMS");
        $this->dom->addChild($ICMSTot, "vICMSDeson",$this->conditionalNumberFormatting($std->vICMSDeson), true, "Valor Total do ICMS desonerado");
        $this->dom->addChild($ICMSTot, "vFCP",$this->conditionalNumberFormatting($std->vFCP), false, "Valor total do ICMS relativo ao Fundo de Combate à Pobreza(FCP)");
        $this->dom->addChild($ICMSTot, "vCOFINS",$this->conditionalNumberFormatting($std->vCOFINS), true, "Valor da COFINS");
        $this->dom->addChild($ICMSTot, "vPIS",$this->conditionalNumberFormatting($std->vPIS), true, "Valor do PIS");
        $this->dom->addChild($ICMSTot, "vFUNTTEL",$this->conditionalNumberFormatting($std->vFUNTTEL), true, "Valor do FUNTTEL");
        $this->dom->addChild($ICMSTot, "vFUST",$this->conditionalNumberFormatting($std->vFUST), true, "Valor do FUST");
        $this->dom->appChild($this->total, $ICMSTot, 'Falta tag "total"');
        return $ICMSTot;
    }

    public function tagVRetTribTot(stdClass $std)
    {
        $possible = [
            'vRetPIS',
            'vRetCofins',
            'vRetCSLL',
            'vIRRF',
            'vDesc',
            'vOutro',
            'vNF',
        ];
        if (isset($std)) {
            $std = $this->equilizeParameters($std, $possible);
        }

        $vRetTribTot = $this->dom->createElement("vRetTribTot");
        $this->dom->addChild($vRetTribTot, "vRetPIS",$this->conditionalNumberFormatting($std->vRetPIS), true, "Valor do PIS retido ");
        $this->dom->addChild($vRetTribTot, "vRetCofins",$this->conditionalNumberFormatting($std->vRetCofins), true, "Valor do COFNS retido ");
        $this->dom->addChild($vRetTribTot, "vRetCSLL",$this->conditionalNumberFormatting($std->vRetCSLL), true, "Valor da CSLL retida");
        $this->dom->addChild($vRetTribTot, "vIRRF",$this->conditionalNumberFormatting($std->vIRRF), false, "Valor do IRRF retido ");
        $this->dom->addChild($vRetTribTot, "vDesc",$this->conditionalNumberFormatting($std->vDesc), true, "Valor Total do Desconto");
        $this->dom->addChild($vRetTribTot, "vOutro",$this->conditionalNumberFormatting($std->vOutro), true, "Outras Despesas acessórias");
        $this->dom->addChild($vRetTribTot, "vNF",$this->conditionalNumberFormatting($std->vNF), true, "Valor Total da NFCom");
        $this->dom->appChild($this->total, $vRetTribTot, 'Falta tag "total"');
        return $vRetTribTot;
    }

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
        $this->dom->addChild($this->gFidelidade, "qtdSaldoPts", $std->qtdSaldoPts, true, $identificador . "Saldo de pontos do cliente na data de referência");
        $this->dom->addChild($this->gFidelidade, "dRefSaldoPts", $std->dRefSaldoPts, true, $identificador . "Data de aferição do saldo de pontos");
        $this->dom->addChild($this->gFidelidade, "qtdPtsResg", $std->qtdPtsResg, true, $identificador . "Qtd de pontos resgatados na data de referência");
        $this->dom->addChild($this->gFidelidade, "dRefResgPts", $std->dRefResgPts, true, $identificador . "Data de resgate dos pontos");
        return $this->gFidelidade;
    }

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
        $this->dom->addChild($this->gFat, "CompetFat", $std->CompetFat, true, $identificador . "Ano e mês referência do faturamento");
        $this->dom->addChild($this->gFat, "dVencFat", $std->dVencFat, true, $identificador . "Data de vencimento da fatura");
        if($std->dPerUsoIni){
            $this->dom->addChild($this->gFat, "dPerUsoIni", $std->dPerUsoIni, true, $identificador . "Período de uso inicial");
        }
        if($std->dPerUsoFim){
            $this->dom->addChild($this->gFat, "dPerUsoFim", $std->dPerUsoFim, true, $identificador . "Período de uso final");
        }
        if($std->codBarras){
            $this->dom->addChild($this->gFat, "codBarras", $std->codBarras, true, $identificador . "Linha digitável do código de barras");
        }
        if($std->codDebAuto){
            $this->dom->addChild($this->gFat, "codDebAuto", $std->codDebAuto, true, $identificador . "Código de autorização débito em conta");
        }
        if($std->codBanco){
            $this->dom->addChild($this->gFat, "codBanco", $std->codBanco, true, $identificador . "Número do banco para débito em conta");
        }
        if($std->codAgencia){
            $this->dom->addChild($this->gFat, "codAgencia", $std->codAgencia, true, $identificador . "Número da agência bancária para débito em conta");
        }
        return $this->gFat;
    }

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
        $this->dom->addChild($enderCorresp, "xLgr", $std->xLgr, true, $identificador . "Logradouro");
        $this->dom->addChild($enderCorresp, "nro", $std->nro, true, $identificador . "Número");
        $this->dom->addChild($enderCorresp, "xCpl", $std->xCpl, true, $identificador . "Complemento");
        $this->dom->addChild($enderCorresp, "xBairro", $std->xBairro, true, $identificador . "Bairro");
        $this->dom->addChild($enderCorresp, "cMun", $std->cMun, true, $identificador . "Código do município");
        $this->dom->addChild($enderCorresp, "xMun", $std->xMun, true, $identificador . "Nome do município");
        $this->dom->addChild($enderCorresp, "CEP", $std->CEP, true, $identificador . "CEP");
        $this->dom->addChild($enderCorresp, "UF", $std->UF, true, $identificador . "Sigla da UF");
        $this->dom->addChild($enderCorresp, "fone", $std->fone, true, $identificador . "Telefone");
        $this->dom->addChild($enderCorresp, "email", $std->email, true, $identificador . "Endereço de E-mail");
        $this->dom->appChild($this->gFat, $enderCorresp, 'Falta tag "gFat"');
        return $enderCorresp;
    }

    public function tagGFatGPIX(stdClass $std)
    {
        $possible = [
            'urlQRCodePIX',
        ];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gPIX> - ';
        $gPIX = $this->dom->createElement("gPIX");
        $this->dom->addChild($gPIX, "urlQRCodePIX", $std->urlQRCodePIX, true, $identificador . "URL do QRCode do PIX que será apresentado na fatura");
        $this->dom->appChild($this->gFat, $gPIX, 'Falta tag "gFat"');
        return $gPIX;
    }

    public function tagGFatCentral(stdClass $std)
    {
        $possible = ['CNPJ','cUF'];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gFatCentral> - ';
        $this->gFatCentral = $this->dom->createElement("gFatCentral");
        $this->dom->addChild($this->gFatCentral, "CNPJ", $std->qtdSaldoPts, true, $identificador . "CNPJ do Emitente centralizador");
        $this->dom->addChild($this->gFatCentral, "cUF", $std->dRefSaldoPts, true, $identificador . "Código da UF do emitente centralizador");

        return $this->gFatCentral;
    }

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

    public function tagInfAdic(stdClass $std)
    {
        $possible = ['infAdFisco', 'infCpl'];
        $std = $this->equilizeParameters($std, $possible);

        $this->infAdic = $this->dom->createElement("infAdic");
        $this->dom->addChild($this->infAdic,"infAdFisco",$std->infAdFisco,false,"Informações Adicionais de Interesse do Fisco");
        $this->dom->addChild($this->infAdic,"infCpl",$std->infCpl,false,"Informações Complementares de interesse do Contribuinte");
        return $this->infAdic;
    }

    public function tagGResptec(stdClass $std)
    {
        $possible = ['CNPJ', 'xContato', 'email', 'fone', 'idCSRT', 'hashCSRT'];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<gRespTec> - ';
        $this->gRespTec = $this->dom->createElement("gRespTec");
        $this->dom->addChild($this->gRespTec, "CNPJ", $std->CNPJ, false, $identificador . "CNPJ da pessoa jurídica responsável técnica pelo sistema utilizado na emissão do documento fiscal eletrônico");
        $this->dom->addChild($this->gRespTec, "xContato", $std->xContato, false, $identificador . "Nome da pessoa a ser contatada");
        $this->dom->addChild($this->gRespTec, "email", $std->email, false, $identificador . "Email da pessoa jurídica a ser contatada");
        $this->dom->addChild($this->gRespTec, "fone", $std->fone, false, $identificador . "Telefone da pessoa jurídica a ser contatada");
        if (!empty($std->idCSRT) && !empty($std->hashCSRT)) {
            $this->dom->addChild($ide, "idCSRT", $std->idCSRT, true, $identificador . "Data e Hora da entrada em contingência");
            $this->dom->addChild($ide, "hashCSRT", $std->hashCSRT, true, $identificador . "Justificativa da entrada em contingência");
        }
        return $this->gRespTec;
    }

    public function tagNfNFComSupl(stdClass $std)
    {
        $possible = ['qrCodNFCom'];
        $std = $this->equilizeParameters($std, $possible);

        $identificador = '<infNFComSupl> - ';
        $infNFComSupl = $this->dom->createElement("infNFComSupl");
        $this->dom->addChild($infNFComSupl, "qrCodNFCom", $std->qrCodNFCom, false, $identificador . "Texto com o QR-Code para consulta da NFCom");
        return $infNFComSupl;
    }

    public function monta(){
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
        if (!empty($this->gSub)) {
            $this->dom->appChild($this->infNFCom, $this->gSub, 'Falta tag "infNFCom"');
        }
        if (!empty($this->gCofat)) {
            $this->dom->appChild($this->infNFCom, $this->gCofat, 'Falta tag "infNFCom"');
        }
        foreach ($this->aDet as $nItem => $det) {
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
            $this->dom->appChild($this->infNFCom, $det, 'Falta tag "infNFCom"');
        }
        if (!empty($this->total)) {
            $this->dom->appChild($this->infNFCom, $this->total, 'Falta tag "infNFCom"');
        }
        if (!empty($this->gFidelidade)) {
            $this->dom->appChild($this->infNFCom, $this->gFidelidade, 'Falta tag "infNFCom"');
        }
        if (!empty($this->gFat)) {
            $this->dom->appChild($this->infNFCom, $this->gFat, 'Falta tag "infNFCom"');
        }
        if (!empty($this->gFatCentral)) {
            $this->dom->appChild($this->infNFCom, $this->gFatCentral, 'Falta tag "infNFCom"');
        }
        foreach ($this->aAutXML as $aut) {
            $this->dom->appChild($this->infNFCom, $aut, 'Falta tag "infNFCom"');
        }
        if (!empty($this->infAdic)) {
            $this->dom->appChild($this->infNFCom, $this->infAdic, 'Falta tag "infNFCom"');
        }
        if (!empty($this->gRespTec)) {
            $this->dom->appChild($this->infNFCom, $this->gRespTec, 'Falta tag "infNFCom"');
        }

        $this->dom->appChild($this->NFCom, $this->infNFCom, 'Falta tag "NFCom"');
        $this->dom->appChild($this->NFCom, $this->infNFComSupl, 'Falta tag "NFCom"');
        $this->dom->appendChild($this->NFCom);

        $this->xml = $this->dom->saveXML();
        if (count($this->errors) > 0) {
            throw new RuntimeException('Existem erros nas tags. Obtenha os erros com getErrors().');
        }
        return $this->xml;
    }

    protected function buildNFCom()
    {
        if (empty($this->NFCom)) {
            $this->NFCom = $this->dom->createElement("NFCom");
            $this->NFCom->setAttribute("xmlns", "http://www.portalfiscal.inf.br/nfco");
        }
        return $this->NFCom;
    }

    protected function equilizeParameters(stdClass $std, $possible)
    {
        return Strings::equilizeParameters($std, $possible, $this->replaceAccentedChars);
    }

    protected function conditionalNumberFormatting($value = null, $decimal = 2)
    {
        if (is_numeric($value)) {
            return number_format($value, $decimal, '.', '');
        }
        return null;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getXML()
    {
        if (empty($this->xml)) {
            $this->monta();
        }
        return $this->xml;
    }
}

