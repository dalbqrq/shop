<?php

	include 'env.php';

	sleep(2);
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $urlPagseguro . "transactions/". $_POST['id'] . "?email=". $emailPagseguro . "&token=" . $tokenPagseguro);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml; charset=ISO-8859-1'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	$dataXML = simplexml_load_string($data);

/* daniel */

	$formatedDataJson = json_encode($dataXML, JSON_PRETTY_PRINT) . "\n";
	$fileName = "log/pagto/" . $dataXML->code;
	$file = fopen($fileName, "ab");
	fwrite($file, $formatedDataJson);
	fclose($file);

/* ------ */

	header('Content-Type: application/json; charset=UTF-8');
	$data = (json_encode($dataXML));
	//echo (json_decode($data)->status);
	echo $data;
	curl_close($ch);
