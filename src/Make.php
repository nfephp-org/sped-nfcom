<?php

namespace NFePHP\NFCom;

use NFePHP\NFCom\traits\TagIde;
use NFePHP\Common\DOMImproved as Dom;

class Make
{
    use TagIde;

    /**
     * @var \DOMElement
     */
    protected $ide;

    public function __construct()
    {
        $this->dom = new Dom('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = false;

    }

    public function tagInfNFCom()
    {

    }



    public function tagEmit()
    {

    }

    public function tagDest()
    {

    }

    public function tagAssinante()
    {

    }

    public function tagGSub()
    {

    }

    public function tagGCofat()
    {

    }

    public function tagDet()
    {

    }

    public function tagProd()
    {

    }

    public function tagICMS()
    {

    }

    public function tagICMSSN()
    {

    }

    public function tagICMSUFDest()
    {

    }

    public function tagPIS()
    {

    }

    public function tagCOFINS()
    {

    }

    public function tagFUST()
    {

    }

    public function tagFUNTTEL()
    {

    }

    public function tagRetTrib()
    {

    }

    public function tagGProcRef()
    {

    }

    public function tagGProc()
    {

    }

    public function tagGRessarc()
    {

    }

    public function tagTotal()
    {

    }

    public function tagGFidelidade()
    {

    }

    public function tagGFat()
    {

    }

    public function tagGFatCentral()
    {

    }

    public function tagAutXML()
    {

    }

    public function tagInfAdic()
    {

    }

    public function tagGResptec()
    {

    }

    public function tagNfNFComSupl()
    {

    }
}