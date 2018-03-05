<?php

require_once('PHPMailer/class.phpmailer.php');
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded


class MedMail
{

    // Log
    const EMAIL_NOTIFICATION_LOG_FILE = "log/emailNotification.log";

    function MedMail() { 

        $this->SmtpServer = 'mail.meuestilodecor.com.br';
        $this->SmtpPort   = 26;
        $this->SmtpUser   = 'simonecollet@meuestilodecor.com.br';
        $this->SmtpPass   = 'lu110972';
        $this->from       = 'simonecollet@meuestilodecor.com.br';
        $this->fromName   = 'Simone Collet';

    }

    function SendMail ($to, $toName, $subject, $bodyHtml, $bodyTxt, $attachFile = '') {

		error_reporting(E_STRICT);
		date_default_timezone_set('America/SaoPaulo');


		$mail = new PHPMailer();
		$mail->IsSMTP();                          // telling the class to use SMTP
		$mail->SMTPDebug  = 1;                    // enables SMTP debug information (for testing)
                                                  // 1 = errors and messages
                                                  // 2 = messages only
        $mail->Host = $this->SmtpServer;
        $mail->Port = $this->SmtpPort;
        $mail->SMTPAuth = true;
        $mail->Username = $this->SmtpUser;
        $mail->Password = $this->SmtpPass;
        $mail->setFrom($this->from, $this->fromName);
        $mail->addReplyTo($this->from, $this->fromName);
        $mail->CharSet = 'UTF-8';

		// CONTEUDO
        //$mail->ContentType = 'text/plain';
        //$mail->Encoding = 'quoted-printable';
        $mail->Subject = $subject;
        $mail->addAddress($to, $toName);
        $mail->AltBody = $bodyTxt;

		//$body             = file_get_contents('contents.html');
		//$body             = eregi_replace("[\]",'',$body);
		//$mail->MsgHTML($body);
        $mail->msgHTML($bodyHtml);
		// CONTEUDO

		//$mail->AddAttachment("images/phpmailer.gif");      // attachment
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

		if(!$mail->Send()) {
				echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
				echo "Message sent!";
		}


        //send the message, check for errors
        if ($mail->SMTPDebug <> 0) {
			echo $result;
        }

		self::LogMail ($to, $subject, $result);
    }


    function LogMail($to, $subject, $result) {

        date_default_timezone_set('America/Sao_Paulo');
        $today = date('Y-m-d');
        $hour = date('H:i:s');

        $log = $today." - ".$hour.",".$to.",".$subject.",".$result."\n";

        $fileName = self::EMAIL_NOTIFICATION_LOG_FILE;
        // Write into log file
        $file = fopen($fileName, "ab");
        fwrite($file, $log);
        fclose($file);

	}


    function TestMail($msg) {

		$to = 'dalbqrq@gmail.com';
		$toName = 'Daniel';
		$subject = 'MedMail class test.';
		$bodyHtml = $msg;
		$bodyTxt = $msg;

		self::SendMail ($to, $toName, $subject, $bodyHtml, $bodyTxt, $attachFile = '');

	}

}


?>
