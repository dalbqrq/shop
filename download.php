
<?php
/**************************************************************
    Code based on: http://stackoverflow.com/questions/10997516/how-to-hide-the-actual-download-folder-location
    See also: 

**************************************************************/

require_once "MedLibrary/MedLibrary.php";


class DownloadProduct
{

    const DOWNLOAD_LOG_FILE = "log/download.log";

    public static function main()
    {

        $ref = (isset($_GET['ref']) && trim($_GET['ref']) !== "" ?
            trim($_GET['ref']) : null);

        $transaction_id = (isset($_GET['transaction_id']) && trim($_GET['transaction_id']) !== "" ?
            trim($_GET['transaction_id']) : null);

        $transaction = MedLibrary::getMedTransaction($transaction_id);

            self::logDownload($transaction);
        if ( $transaction->result == "OK" ) {

            $dadosProduto = json_decode(decodificar($ref));

            header("Content-Type: application/octet-stream");

            $filePath = 'data/';
            $fileName = $dadosProduto->e_product; // Nome que será visto pelo usuário
            $fileFake = $dadosProduto->real_filename; // Nome do arquivo gravado no servidor
            $realFileName = $fileName;
            $fakeFileName = $filePath . $fileFake;

/*
        Incluir teste se existe arquivo como no exemplo abaixo

            if (file_exists($file) && is_readable($file) && preg_match('/\.pdf$/',$file)) {
            header('Content-Type: application/pdf');
                    header("Content-Disposition: attachment; filename=\"$file\"");
                readfile($file);
            }
            } else {
                header("HTTP/1.0 404 Not Found");
                echo "<h1>Error 404: File Not Found: <br /><em>$file</em></h1>";
            }
*/

            header("Content-Disposition: attachment; filename=" . urlencode($fakeFileName));   
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header("Content-Disposition: attachment; filename=$realFileName");            
            header("Content-Description: File Transfer");            
            header("Content-Length: " . filesize($fakeFileName));
            flush(); // this doesn't really matter.

            $fp = fopen($fakeFileName, "r");
            while (!feof($fp))
            {
                echo fread($fp, 65536);
                flush(); // this is essential for large downloads
            } 
            fclose($fp); 

        }
        // TODO - incluir opcoes caso $result <> "OK"

        exit;
    }


    public static function logDownload(MedTransaction $transaction)
    {
        // Extract payment transaction code from url
        date_default_timezone_set('America/Sao_Paulo');
        $today = date('Y-m-d');
        $hour = date('H:i:s');

        $origin = $transaction->origin;
        $code = $transaction->code;
        $status = $transaction->status;
        $statusName = $transaction->statusName;
        $result = $transaction->result;

        $itemId = $transaction->itemId;
        $desc = $transaction->description;
        $price = $transaction->price;
        $name = $transaction->name;
        $email = $transaction->email;
        $reference = $transaction->reference;

        $log = $today." ".$hour.","
            .$origin.",".$code.",".$statusName.",".$result.","
            .$itemId.",".$desc."," .$price.",".$name.",".$email.",".$reference."\n";

        // Write into log file
        $file = fopen(self::DOWNLOAD_LOG_FILE, "ab");
        fwrite($file, $log);
        fclose($file);
    }

}


DownloadProduct::main();


?>
