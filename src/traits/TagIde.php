<?php

namespace NFePHP\NFCom\traits;

use DOMElement;
use stdClass;
use NFePHP\NFCom\Common\Aux;
use NFePHP\NFCom\Common\Keys;

trait TagIde
{
    public function tagIde(stdClass $std): DOMElement
    {
        $possible = [
            'cUF',
            'tpAmb',
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
            'indNotaEntrada',
            'dhCont',
            'xJust'
        ];
        $std = Aux::equilizeParameters($std, $possible);

        if (empty($std->cNF)) {
            $std->cNF = Keys::random($std->nNF);
        }
        if (empty($std->cDV)) {
            $std->cDV = 0;
        }
        $this->tpAmb = $std->tpAmb;
        $this->mod = $std->mod;
        $identificador = '[4] <ide> - ';
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
            '62',
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
            $identificador . "Dígito Verificador da Chave de Acesso da NFCom"
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
            $identificador . "Forma de emissão do Documento Fiscal"
        );
        $this->dom->addChild(
            $ide,
            "nSiteAutoriz",
            $std->nSiteAutoriz,
            true,
            $identificador . "Número do Site do Autorizador da NFCom"
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
            $identificador . "Versão do aplicativo emissor da NFCom"
        );
        $this->dom->addChild(
            $ide,
            "indPrePago",
            $std->indPrePago ?? null,
            false,
            $identificador . "Indicador de serviço pré-pago"
        );
        $this->dom->addChild(
            $ide,
            "indCessaoMeiosRede",
            $std->indCessaoMeiosRede ?? null,
            false,
            $identificador . "Indicador de Sessão de Meios de Rede"
        );
        $this->dom->addChild(
            $ide,
            "indNotaEntrada",
            $std->indNotaEntrada ?? null,
            false,
            $identificador . "Indicador de nota de entrada<"
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
}
