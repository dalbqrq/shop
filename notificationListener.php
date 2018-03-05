<?php
/*
 ************************************************************************
  Code created by Daniel Albuquerque
  baseb on notificationListener.php from PagSeguro examples.
 ************************************************************************
 */
require_once 'MedLibrary/MedLibrary.php';

class NotificationListener
{

    const NOTIFICATION_ACCESS_LOG_FILE = "log/notificationListener_access.log";

    public static function main()
    {
        $code = (isset($_POST['notificationCode']) && trim($_POST['notificationCode']) !== "" ?
            trim($_POST['notificationCode']) : null);
        $type = (isset($_POST['notificationType']) && trim($_POST['notificationType']) !== "" ?
            trim($_POST['notificationType']) : null); // Only TRANSACTION notification are sent !!!

        if ($code && $type) {
            $notificationType = new PagSeguroNotificationType($type);
            $strType = $notificationType->getTypeFromValue();

            self::logNotification($strType, $code);

            if ($strType == 'TRANSACTION') {
                self::transactionNotification($code);
            } else {
                LogPagSeguro::error("Unknown notification type [".$notificationType->getValue()."]");
            }

        } else {
            LogPagSeguro::error("Invalid notification parameters.");
        }
    }


    private static function transactionNotification($notificationCode)
    {
        try {
            $transaction = MedLibrary::checkMedTransaction($notificationCode);
            MedLibrary::deliverTransaction($transaction);

        }
        catch (PagSeguroServiceException $e) {
            LogPagSeguro::error("Service exception.");
            die($e->getMessage());
        }
    }


    private static function logNotification($strType, $code)
    {
        // Create log info
        date_default_timezone_set('America/Sao_Paulo');
        $today = date('Y-m-d');
        $hour = date('H:i:s');
        $id = str_replace('-', '', $code);
        $log = $today." ".$hour.",".$strType.",".$id."\n";

        $fileName = self::NOTIFICATION_ACCESS_LOG_FILE;

        // Write into log file
        $file = fopen($fileName, "ab");
        fwrite($file, $log);
        fclose($file);
    }

}

NotificationListener::main();
