<?php
require_once('./vendor/autoload.php');

use NFePHP\NFCom\Make;

$nfcom = new Make();

$data = [
	'Id' => 1,
	'versao' => 1,
    'cUF'=> 1,
    'tpAmb'=> 1,
    'mod'=> 1,
    'serie'=> 1,
    'nNF'=> 1,
    'cNF'=> 1,
    'cDV'=> 1,
    'dhEmi'=> 1,
    'tpEmis'=> 1,
    'nSiteAutoriz'=> 1,
    'cMunFG'=> 1,
    'finNFCom'=> 1,
    'tpFat'=> 1,
    'verProc'=> 1,
    'indPrePago'=> 1,
    'indCessaoMeiosRede'=> 1,
    'dhCont'=> 1,
    'xJust'=> 1,
];

$std = new \stdClass();
$std->Id = $data['Id'] ?? null;
$std->versao = $data['versao'] ?? null;
$nfcom->tagInfNFCom($std);

$std = new \stdClass();
$std->cUF = $data['cUF'] ?? null;
$std->tpAmb = $data['tpAmb'] ?? null;
$std->mod = $data['mod'] ?? null;
$std->serie = $data['serie'] ?? null;
$std->nNF = $data['nNF'] ?? null;
$std->cNF = $data['cNF'] ?? null;
$std->cDV = $data['cDV'] ?? null;
$std->dhEmi = $data['dhEmi'] ?? null;
$std->tpEmis = $data['tpEmis'] ?? null;
$std->nSiteAutoriz = $data['nSiteAutoriz'] ?? null;
$std->cMunFG = $data['cMunFG'] ?? null;
$std->finNFCom = $data['finNFCom'] ?? null;
$std->tpFat = $data['tpFat'] ?? null;
$std->verProc = $data['verProc'] ?? null;
$std->indPrePago = $data['indPrePago'] ?? null;
$std->indCessaoMeiosRede = $data['indCessaoMeiosRede'] ?? null;
$std->dhCont = $data['dhCont'] ?? null;
$std->xJust = $data['xJust'] ?? null;
$nfcom->tagIde($std);

try {
	$nfcom->monta();
} catch (\Exception $e) {
	$error['nNF'] = $data['nNF'];
	$error['erros'] = $nfcom->getErrors();
	throw new \Exception(json_encode($error), 400);
}

var_dump($nfcom);

// header('Content-type: text/xml');
// header('Content-Disposition: attachment; filename="nfcom.xml"');
// echo $nfcom->getXML();

