<?php

	include "env.php";
	require_onde "templates/dadosProduto.php";

	$result = (isset($_GET['result']) && trim($_GET['result']) !== "" ?
        trim($_GET['result']) : null);

	$t_product = (isset($_GET['t_product']) && trim($_GET['t_product']) !== "" ?
        trim($_GET['t_product']) : null);

	$ref = (isset($_GET['ref']) && trim($_GET['ref']) !== "" ?
        trim($_GET['ref']) : null);

	$buy  = $siteURL . "/" . $shopDir . "/paymentRequest.php";

	if ( $result == "OK" || $result == "ON_GOING" ) {

		$dadosProduto = json_decode(decodificar($ref));
		//$url = $siteURL . "/" . $dadosProduto->return_url;
		$url = $dadosProduto->return_url;

	} elseif ( $result == "PROBLEM" ) {

        	echo "<p>Desculpe-nos!</p>";
        	echo "<p>Ocorreu um problema na compra do seu " . $t_product . "</p>";
        	echo "<p>Tente realizar a compra novamente clicando no link abaixo. Mas não se preocupe. Você não será cobrada duas vezes. Se isso ocorrer nós devolveremos o seu dinheiro.</p>";
        	echo "<a href='" . $buy . "?ref=" . $ref . "'> " . $t_product . "</a>";
	
		$url = $siteURL . "/problema-compra";
   
	} else {

        	echo "<p>Desculpe-nos!</p>";
        	echo "<p>Alguma coisa de errado aconteceu...";   
        	echo "<p>Se você estava tentando comprar um produto do MeuEstiloDecor, clique no link abaixo para retornar à página principal do site e iniciar o processo de compra novamente. Mas não se preocupe. Você não será cobrada duas vezes. Se isso ocorrer nós devolveremos o seu dinheiro.</p>";
        	echo "<a href='$siteURL'> MeuEstiloDecor</a>";

		$url = $siteURL . "/erro-compra";
	}

	// Redireciona para destino final
	header("Location: " . $url . "?t_product=" . $t_product . "&result=" . $result . "&buy=" . $buy . "&ref=" . $ref);
	exit();
