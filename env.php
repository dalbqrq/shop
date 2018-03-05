<?php

/*
Arquivo de configuração do ambiente
*/

$sandBox = false;

if ($sandBox) {
	# Dados de configuracao do ambiente de Testes (Sandbox)
	$scriptPagseguro = "https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js";
	$urlPagseguro = "https://ws.sandbox.pagseguro.uol.com.br/v2/";
	$tokenPagseguro = "D2E62C1F8F0242B0A5D002E816116538";
	$shopDir = "shop";

} else {
	# Dados de configuracao do ambiente de Producao
	$scriptPagseguro = "https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js";
	$urlPagseguro = "https://ws.pagseguro.uol.com.br/v2/";
	$tokenPagseguro = "74E84D8F4F1F4CE5A37520A66699F26C";
	$shopDir = "shop";
}


# Dados deconfiguracao comuns entre Sandbox e Producao
$emailPagseguro = "dalbqrq@gmail.com";
$siteURL = "https://meuestilodecor.com.br";
$notificationURL = "$siteURL/$shopDir/notificationListener.php";


