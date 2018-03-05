<?php

class DownloadBribe
{
	const DOWNLOAD_LOG_FILE = "log/download_pdf.log";

	public static function main()
	{
		$secret_id = (isset($_GET['secret_id']) && trim($_GET['secret_id']) !== "" ?
			trim($_GET['secret_id']) : null);
		$email = (isset($_GET['email']) && trim($_GET['email']) !== "" ?
			trim($_GET['email']) : null);
		$ad_tracking = (isset($_GET['ad_tracking']) && trim($_GET['ad_tracking']) !== "" ?
			trim($_GET['ad_tracking']) : null);

                $secret_ok = false;
		$secret_passos   = "HpucLbJrx3yKbFrG9qKfF92u6Aa9s";
		$secret_ebook    = "BpucLbJrx2yKbFrG0qKfF92s6Aa0t";
		$secret_estante  = "pUckanbdo2yKbFrG0qKfF18u4Aa0t";

		if ( $secret_id == $secret_passos ) {
                    $secret_ok = true;
		    $file = './data/bribe-7-passos.pdf';
		    $filename = '7-Passos-MeuEstiloDecor.pdf'; /* Note: Always use .pdf at the end. */
                } 

		if ( $secret_id == $secret_ebook ) {
                    $secret_ok = true;
		    $file = './data/Ebook-Roteiro-Decoracao-Pra-Vida-Real.pdf';
		    $filename = 'Ebook-Roteiro-Decoracao-Pra-Vida-Real.pdf'; /* Note: Always use .pdf at the end. */
                } 

		if ( $secret_id == $secret_estante ) {
                    $secret_ok = true;
		    $file = './data/Guia-Arrume-Sua-Estante-Como-Designer.pdf';
		    $filename = 'Guia-Arrume-Sua-Estante-Como-Designer.pdf'; /* Note: Always use .pdf at the end. */
                } 

		if ( $secret_ok  ) {
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="' . $filename . '"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize($file));
			header('Accept-Ranges: bytes');

			@readfile($file);
			self::logDownload($email, $ad_tracking);
		} else {
			echo "<head><title>MeuEstiloDecor</title><head><body>MeuEstiloDecor - Conteudo nao disponivel</body>";
		}

		exit;
	}

	public static function logDownload($email, $ad_tracking)
	{
		date_default_timezone_set('America/Sao_Paulo');
		$ip = $_SERVER['REMOTE_ADDR'];
		$today = date('Y-m-d');
		$hour = date('H:i:s');
		$log = $today." ".$hour . "," . $ip . "," . $email . "," . $ad_tracking . "\n";

		// Write into log file
		$file = fopen(self::DOWNLOAD_LOG_FILE, "ab");
		fwrite($file, $log);
		fclose($file);
	}
}

DownloadBribe::main();

?>
