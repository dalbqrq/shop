<?php   
        
//require_once "MedLibrary/MedLibrary.php";
require_once "MedLibrary/MedMail.php";
        
    echo "Hi";
        
    $SMTPMail = new MedMail();
    echo "Hey";
    $SMTPMail->TestMail("Meu teste de daniel.impa.br"); 
        
    echo "Ho";
        
?>   
