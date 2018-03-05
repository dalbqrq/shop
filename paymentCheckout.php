<?php
/*
 ************************************************************************
  Code created by Daniel Albuquerque
  baseb on searchTransactionByCode.php from examples.
 ************************************************************************
 */

//require_once 'PagSeguroLibrary/PagSeguroLibrary.php';
require_once 'MedLibrary/MedLibrary.php';

class PaymentCheckout
{

    const NOTIFICATION_ACCESS_LOG_FILE = "log/paymentCheckout_access.log";

    public static function main()
    {
        //
        // TO DEBUG, USE: 
        // meuestilodecor.com.br/shop/paymentCheckout.php?transaction_id=7105AC24-C2BE-4F30-9760-F3089ABD40F2
        //

        $transaction_id = (isset($_GET['transaction_id']) && trim($_GET['transaction_id']) !== "" ?
            trim($_GET['transaction_id']) : null);

        self::logNotification($transaction_id);

        try {
            $transaction = MedLibrary::getMedTransaction($transaction_id);
            MedLibrary::deliverTransaction($transaction);
        }
        catch (PagSeguroServiceException $e) {
            LogPagSeguro::error("Service exception.");
            die($e->getMessage());
        }
    }



    private static function logNotification($transaction_id)
    {
        // Create log info
        date_default_timezone_set('America/Sao_Paulo');
        $today = date('Y-m-d');
        $hour = date('H:i:s');
        $id = str_replace('-', '', $transaction_id);
        $log = $today." ".$hour.",".$id."\n";

        $fileName = self::NOTIFICATION_ACCESS_LOG_FILE;

        // Write into log file
        $file = fopen($fileName, "ab");
        fwrite($file, $log);
        fclose($file);
    }



}

PaymentCheckout::main();
