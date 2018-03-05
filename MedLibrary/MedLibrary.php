<?php
/*
 ************************************************************************
  Code created by Daniel Albuquerque
 ************************************************************************
 */

require_once "PagSeguroLibrary/PagSeguroLibrary.php";
require_once "MedLibrary/MedMail.php";
require_once "templates/dadosProduto.php";


class MedConfig
{

	public $ENVIRONMENT = 'production';
	public $BASEURL='https://meuestilodecor.com.br';

	//public $ENVIRONMENT = 'sandbox';
	//public $BASEURL='https://meuestilodecor.com.br';

}

class MedProduct
{

    //
    // Returns Product information given a product reference
    //
	// $product is an array indexed by numbers containing:
	//	0 - REF				- product reference code
	//	1 - NAME			- product name
	//	2 - PRICE			- product price
	//	3 - PRICE_TYPE		- if price is 'FULL' or with 'DISCOUNT'
	//	4 - PRODUCT_URL		- relative url for the product's landing page
	//	5 - RET_URL			- relative url for the "thanks" page - this must be a template for all return cases
	//	6 - E-PRODUCT		- e-product name seen by the user
	//	7 - REAL_FILENAME	- e-product file name stored on site
	//	8 - RET_MAIL		- template file for return email - this must be a template for all return cases
	//
	//
    public static function getProduct($productRef)
    {
        if (!isset($productRef)) {
            self::redirectUrlError("no product reference");
        }

        // Retrieve product information
	$product = json_decode(decodificar($productRef));

	if ( $product->ref != $productRef ) { echo "Produto nao encontrado\n"; }
        //if ( $dadosProduto->ref != $productRef ) { self::redirectUrlError("product not found"); }

        return $product;
    }
            
    //
    // Redirects to error page
    //
    public static function redirectUrlError($message)
    {

	$conf = new MedConfig();
        $url = $conf->BASEURL . '/produtos';
        //header ('Location: '.$url);
        die(" Error: $message - Redirect to: $url");
    }
}


class MedTransaction
{
    // See xml transaction example at the end of this file

    const TRANSACT_CHECKOUT_LOG_FILE = "log/paymentCheckout.log";
    const TRANSACT_NOTIFICATION_LOG_FILE = "log/notificationListener.log";

    public $code;       // codigo da transacao ou transaction_id
    public $name;       // nome do comprador
    public $email;      // email do comprador
    public $amount;     // valor total da transacao. como só faz venda de 1 produto por vez este valor é igual a price
    public $reference;  // codigo de referencia da transacao setado pela biblioteca MedLibrary

    public $itemCount;  // contagem de itens que deve ser sempre 1
    public $itemId;     // codigo de referencia do produto ($productRef em product.csv)
    public $description; // titulo (nome) do produto
    public $quantity;  // quantidade que deve ser sempre 1 pois só faz venda de 1 produto por vez
    public $price;     // valor do produto

    // $status define the status transaction. Can be one of the following
    /* 
        'INITIATED' => 0,
        'WAITING_PAYMENT' => 1,
        'IN_ANALYSIS' => 2,
        'PAID' => 3,
        'AVAILABLE' => 4,
        'IN_DISPUTE' => 5,
        'REFUNDED' => 6,
        'CANCELLED' => 7,
        'SELLER_CHARGEBACK' => 8,
        'CONTESTATION' => 9
    */
    public $status;
    public $statusName;

    // $result defines a category for the transaction base on $status:
    // status 0, 1, 2       => ON_GOING - Not finished
    // status 3, 4          => OK       - Payd
    // status 5, 6, 7, 8, 9 => PROBLEM  - Some problem happend after been payd
    public $result;

    // $origin defines if this transaction was created from CHECKOUT or NOTIFICATION
    public $origin;

    // $product is defined in products.csv table (file)
    public $product;

    public function init(PagSeguroTransaction $transaction, $origin) {
        $this->code = $transaction->getCode();
        $this->name = $transaction->getSender()->getName();
        $this->email = $transaction->getSender()->getEmail();
        $this->amount = $transaction->getGrossAmount();
        $this->reference = $transaction->getReference();

        // In MeuEstiloDecor there is only one item per transaction
        if ($transaction->getItems()) {
            if (is_array($transaction->getItems())) {
                $this->itemCount = count($transaction->getItems());
                foreach ($transaction->getItems() as $key => $item) {
                    $this->itemId = $item->getId();
                    $this->description = $item->getDescription();
                    $this->quantity = $item->getQuantity();
                    $this->price = $item->getAmount();
                }
            }
        }

        $this->status = $transaction->getStatus()->getValue();

        switch ($this->status) {
            case 0:
                $this->statusName = "INITIATED";
                $this->result = "ON_GOING";  // Não entregar o produto ainda.Pagamento só iniciado.
                $this->statusDesc = "Transação iniciada. Prossiga com o pagamento.";
                break;
            case 1:
                $this->statusName = "WAITING_PAYMENT";
                $this->result = "ON_GOING";  // Normamente é um pagamento eito por boleto. Não entregar o produto ainda.
                $this->statusDesc = "Aguardando pagamento: você iniciou a transação, mas até o momento o PagSeguro não recebeu nenhuma informação sobre o pagamento.";
                break;
            case 2:
                $this->statusName = "IN_ANALYSIS";
                $this->result = "ON_GOING";  // O pagamento está em análise. Não entregar ainda o produto.
                $this->statusDesc = "Em análise: você optou por pagar com um cartão de crédito e o PagSeguro está analisando a transação.";
                break;
            case 3:
                $this->statusName = "PAID";
                $this->result = "OK";  // O valor foi pago e o produto pode ser entregue.
                $this->statusDesc = "Paga: a transação foi paga e o PagSeguro já recebeu uma confirmação da instituição financeira responsável pelo processamento.";
                break;
            case 4:
                $this->statusName = "AVAILABLE";
                $this->result = "NO_PROBLEM";  // Isto ésó um aviso o PAgSeguro que o valor está disponível para saque.
                $this->statusDesc = "Disponível: a transação foi paga e chegou ao final de seu prazo de liberação sem ter sido retornada e sem que haja nenhuma disputa aberta.";
                break;
            case 5:
                $this->statusName = "IN_DISPUTE";
                $this->result = "PROBLEM";
                $this->statusDesc = "Em disputa: você, dentro do prazo de liberação da transação, abriu uma disputa.";
                break;
            case 6: 
                $this->statusName = "REFUNDED";
                $this->result = "PROBLEM";
                $this->statusDesc = "Devolvida: o valor da transação foi devolvido para você.";
                break;
            case 7:
                $this->statusName = "CANCELLED";
                $this->result = "PROBLEM";
                $this->statusDesc = "Cancelada: a transação foi cancelada sem ter sido finalizada.";
                break;
            case 8:
                $this->statusName = "SELLER_CHARGEBACK";
                $this->result = "PROBLEM";
                $this->statusDesc = "Chargeback debitado: o valor da transação foi devolvido para você.";
                break;
            case 9:
                $this->statusName = "CONTESTATION";
                $this->result = "PROBLEM";
                $this->statusDesc = "Em contestação: você abriu uma solicitação de chargeback junto à operadora do cartão de crédito.";
                break;
            default:
                $this->statusName = "UNKNOWN";
                $this->result = "PROBLEM";
                $this->statusDesc = "Status da transação não definido. Por favor entre em contato com simone@meuestilodecor.com.br.";
                break;
        }

        $this->origin = $origin;
        $this->product = MedProduct::getProduct($this->itemId);
    }


    public function logMedTransaction() {

        date_default_timezone_set('America/Sao_Paulo');
        $today = date('Y-m-d');
        $hour = date('H:i:s');

        $origin = $this->origin;
        $code = str_replace('-', '', $this->code);
        $status = $this->status;
        $statusName = $this->statusName;
        $result = $this->result;

        $itemId = $this->itemId;
        $desc = $this->description;
        $price = $this->price;
        $name = $this->name;
        $email = $this->email;
        $reference = $this->reference;

        switch ($origin) {
            case 'CHECKOUT':
                //$fileName = $this->baseDir . self::TRANSACT_CHECKOUT_LOG_FILE;
                $fileName = self::TRANSACT_CHECKOUT_LOG_FILE;
                break;
            case 'NOTIFICATION':
                //$fileName = $this->baseDir . self::TRANSACT_NOTIFICATION_LOG_FILE;
                $fileName = self::TRANSACT_NOTIFICATION_LOG_FILE;
                break;
        }

        $log = $today." - ".$hour."," 
            .$origin.",".$code.",".$statusName.",".$result.","
            .$itemId.",".$desc."," .$price.",".$name.",".$email.",".$reference."\n";

        // Write into log file
        $file = fopen($fileName, "ab");
        fwrite($file, $log);
        fclose($file);
    }

}


class MedLibrary
{
    //
    // Thank you page must automatically register for aweber
    // SEE:
    // http://spamtech.co.uk/tips/automatically-submit-a-form-using-javascript/
    //

	// Log
    const EMAIL_NOTIFICATION_LOG_FILE = "log/emailNotification.log";


    public static function Test()
    {
		$conf = new MedConfig();
		echo '<p>' . $conf->ENVIRONMENT . '<p>';
		echo '<p>' . $conf->BASEURL . '<p>';
	}
	

    public static function getCredentials()
    {
		$conf = new MedConfig();
        // Credentials
        PagSeguroConfig::setEnvironment($conf->ENVIRONMENT);
        return PagSeguroConfig::getAccountCredentials();
    }


    //
    // Return Transaction data given a notification code
    //
    public static function checkMedTransaction($notification_code)
    {
        // VER: http://sounoob.com.br/recebendo-notificacoes-do-pagseguro-usando-php-sem-utilizar-a-biblioteca-oficial/
        // Outra maneira de receber a transacao:
        // https://ws.sandbox.pagseguro.uol.com.br/v3/transactions/notifications/DDEA0A855D6A5D6AA73EE43EFFA79449FF2F
        //                                    ?email=dalbqrq@gmail.com&token=D2E62C1F8F0242B0A5D002E816116538
        //
        // Este tipo de consulta retorna um xml. Usar simplexml_load_string() para ler valores.
        //

        try {
            $credentials = MedLibrary::getCredentials();
            $transaction = PagSeguroNotificationService::checkTransaction($credentials, $notification_code);

        } catch (PagSeguroServiceException $e) {
            die($e->getMessage());
        }
        $medTransaction = new MedTransaction();
        $medTransaction->init($transaction, 'NOTIFICATION');
        return $medTransaction;
    }


    //
    // Return Transaction data given a transaction code
    //
    public static function getMedTransaction($transaction_code)
    {
        try {
            $credentials = self::getCredentials();
            $transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $transaction_code);

        } catch (PagSeguroServiceException $e) {
            die($e->getMessage());
        }
        $medTransaction = new MedTransaction();
        $medTransaction->init($transaction, 'CHECKOUT');
        return $medTransaction;
    }


    public static function deliverTransaction(MedTransaction $transaction)
    {
        //MedLibrary::printMedTransaction($transaction);

	$conf = new MedConfig();
	$baseurl = $conf->BASEURL;
	$retUrl = $transaction->product->return_url;
	$retMail = $transaction->product->return_mail;

	//$THANKS_OK_URL = $baseurl . $retUrl;
	//$THANKS_WAIT_URL = $baseurl . $retUrl;
	//$PROBLEM_URL = $baseurl . $retUrl;
	//$ERROR_URL = $baseurl . $retUrl;

	$THANKS_OK_URL = $retUrl;
	$THANKS_WAIT_URL = $retUrl;
	$PROBLEM_URL = $retUrl;
	$ERROR_URL = $retUrl;

	$EMAIL_OK_FILE = 'data/mail/' . $retMail . '-email_ok';
	$EMAIL_WAIT_FILE = 'data/mail/' . $retMail . '-email_wait';
	$EMAIL_PROBLEM_FILE = 'data/mail/' . $retMail . '-email_problem';
	$EMAIL_ERROR_FILE = 'data/mail/' . $retMail . '-email_error';

	$EMAIL_OK_SUBJECT = 'OBRIGADA por adquirir o ' . $transaction->product->name;
	$EMAIL_WAIT_SUBJECT = 'OBRIGADA por adquirir o ' . $transaction->product->name;
	$EMAIL_PROBLEM_SUBJECT = 'Desculpe-me mas houve um problema.';
	$EMAIL_ERROR_SUBJECT = 'Desculpe-me mas ocorreu um erro...';


        switch ($transaction->result) {
            case 'OK':
                $url = $THANKS_OK_URL;
                $subject = $EMAIL_OK_SUBJECT;
                $file = $EMAIL_OK_FILE;
                break;
            case 'ON_GOING':
                $url = $THANKS_WAIT_URL;
                $subject = $EMAIL_WAIT_SUBJECT;
                $file = $EMAIL_WAIT_FILE;
                break;
            case 'PROBLEM':
                $url = $PROBLEM_URL;
                $subject = $EMAIL_PROBLEM_SUBJECT;
                $file = $EMAIL_PROBLEM_FILE;
                break;
            case 'ERROR': // TODO - é mesmo necessário?
                $url = $ERROR_URL;
                $subject = $EMAIL_ERROR_SUBJECT;
                $file = $EMAIL_ERROR_FILE;
                break;
        }

        $code = $transaction->code;        // transaction_id
        $result = $transaction->result;    // resultado da transacao
        $itemId = $transaction->itemId;    // Ref - referencia do prodto na tabela de produtos
        $desc = $transaction->description; // t_product ou titulo do porduto
        $name = $transaction->name;        // nome comprador
        $email = $transaction->email;      // email do comprador
        if ( $result == "OK" ) {
            $e_product = $transaction->product->e_product;      // nome do arquivo do e-product
        } else {
            $e_product = "";                             // transacao nao OK entao nao envia nome do arquivo
        }

        // TODO - Caso PROBLEM, a página 'obrigada' e o email podem informar o que está acontecendo.

        // TODO - incluir na url ($buy) o endereco da landing page do produto ($product[4] = PRODUCT_URL). Usado caso PROBLEM.
        $url_params = 'ref=' . $itemId . '&result=' . $result . '&t_product=' . $desc . '&e_product=' . $e_product . '&name=' 
                    . $name . '&transaction_id=' . $code;
        $url = str_replace(' ', '%20', $url . '?' . $url_params);


        // Send the confirmation email
        // --  Não precisa enviar email pois o processo de notificação se encarregará disso !!! --
        if ( $transaction->result == 'OK' || $transaction->result == 'ON_GOING' ) {
            if ( $transaction->origin == 'NOTIFICATION' ) {
                self::sendEmail($transaction, $subject, $file, $url);
            }
        }

        $transaction->logMedTransaction();

        // Redirect to thanks/download page
        if ( $transaction->origin == 'CHECKOUT' ) {
            //TODO - echo '<p> Redirect to url = ' . $url . '</p>';
            header ('Location: '.$url);
        }
        
    }


    public static function sendEmail(MedTransaction $transaction, $subject, $file, $url)
    {
		$conf = new MedConfig();

        if ( $conf->ENVIRONMENT == 'sandbox' ) {
            $to = 'dalbqrq@gmail.com';
        } else {
            $to = $transaction->email;
        }
        $arrToName = explode(' ',trim($transaction->name));  // Explode in array
        $toName = $arrToName[0];                             // First name only

        $transaction_id = $transaction->code;
        $t_product = $transaction->description;
        $e_product = $transaction->product->e_product;
        // TODO - verificar esta url
        $download = $url;

        $bodyHtml = file_get_contents($file . '.html');
        $bodyTxt =  file_get_contents($file . '.txt');

        // TODO - incluir na url ($buy) o endereco da landing page do produto ($product[4] = PRODUCT_URL). Usado caso PROBLEM.
        $bodyHtml = str_replace('[name]', $toName, $bodyHtml);
        $bodyHtml = str_replace('[t_product]', $t_product, $bodyHtml);
        $bodyHtml = str_replace('[download]', $download, $bodyHtml);
        $bodyHtml = str_replace('[transaction_id]', $transaction_id, $bodyHtml);
        $bodyHtml = str_replace('[e_product]', $e_product, $bodyHtml);

        // TODO - incluir na url ($buy) o endereco da landing page do produto ($product[4] = PRODUCT_URL). Usado caso PROBLEM.
        $bodyTxt = str_replace('[name]', $toName, $bodyTxt);
        $bodyTxt = str_replace('[t_product]', $t_product, $bodyTxt);
        $bodyTxt = str_replace('[download]', $download, $bodyTxt);
        $bodyTxt = str_replace('[transaction_id]', $transaction_id, $bodyTxt);
        $bodyTxt = str_replace('[e_product]', $e_product, $bodyTxt);

        // TODO - Pode-se incluir no email WAIT um link para paymentCheckout($transaction_id).


        $SMTPMail = new MedMail();
        $SMTPMail->SendMail($to, $toName, $subject, $bodyHtml, $bodyTxt);

    }


    //
    // Outputs medTransaction data for debugging purposes
    //
    public static function printMedTransaction(MedTransaction $transaction)
    {
        echo "Code: " . $transaction->code . "</br>";
        echo "Reference: " . $transaction->reference . "</br>";
        echo "Name: " . $transaction->name . "</br>";
        echo "Email: " . $transaction->email . "</br>";
        echo "Item ID: " . $transaction->itemId . "</br>";
        echo "Description: " . $transaction->description . "</br>";
        echo "Prod. price: " . $transaction->price . "</br>";
        echo "E-Product: " . $transaction->product->e_product . "</br>";
        echo "Status: " . $transaction->statusName . "</br>";
        echo "Result: " . $transaction->result . "</br>";
        echo "Result Description: " . $transaction->statusDesc . "</br>";
    }


    //
    // Outputs transaction data for debugging purposes
    //
    public static function printTransaction(PagSeguroTransaction $transaction)
    {
        echo "<h2>Transaction search by code result";
        echo "<h3>Code: " . $transaction->getCode() . '</h3>';
        echo "<h3>Status: " . $transaction->getStatus()->getTypeFromValue() . '</h3>';
        echo "<h3>StatusValue: " . $transaction->getStatus()->getValue() . '</h3>';
        echo "<h4>Reference: " . $transaction->getReference() . "</h4>";

        echo "grossAmount: " . $transaction->getGrossAmount() . '<br>';
        echo "discountAmount: " . $transaction->getDiscountAmount() . '<br>';
        echo "installmentCount: " . $transaction->getInstallmentCount() . '<br>';

        if ($transaction->getCreditorFees()) {
            echo "<h4>CreditorFees:</h4>";
            echo "intermediationRateAmount: " . $transaction->getCreditorFees()->getIntermediationRateAmount() . '<br>';
            echo "intermediationFeeAmount: " . $transaction->getCreditorFees()->getIntermediationFeeAmount() . '<br>';
        }

        if ($transaction->getItems()) {
            echo "<h4>Items:</h4>";
            if (is_array($transaction->getItems())) {
                foreach ($transaction->getItems() as $key => $item) {
                    echo "Id: " . $item->getId() . '<br>'; // prints the item id, e.g. I39
                    echo "Description: " . $item->getDescription() .
                        '<br>'; // prints the item description, e.g. Notebook prata
                    echo "Quantidade: " . $item->getQuantity() . '<br>'; // prints the item quantity, e.g. 1
                    echo "Amount: " . $item->getAmount() . '<br>'; // prints the item unit value, e.g. 3050.68
                    echo "<hr>";
                }
            }
        }

        if ($transaction->getSender()) {
            echo "<h4>Sender data:</h4>";
            echo "Name: " . $transaction->getSender()->getName() . '<br>';
            echo "Email: " . $transaction->getSender()->getEmail() . '<br>';
            if ($transaction->getSender()->getPhone()) {
                echo "Phone: " . $transaction->getSender()->getPhone()->getAreaCode() . " - " .
                    $transaction->getSender()->getPhone()->getNumber();
            }
        }

        if ($transaction->getShipping()) {
            echo "<h4>Shipping information:</h4>";
            if ($transaction->getShipping()->getAddress()) {
                echo "Postal code: " . $transaction->getShipping()->getAddress()->getPostalCode() . '<br>';
                echo "Street: " . $transaction->getShipping()->getAddress()->getStreet() . '<br>';
                echo "Number: " . $transaction->getShipping()->getAddress()->getNumber() . '<br>';
                echo "Complement: " . $transaction->getShipping()->getAddress()->getComplement() . '<br>';
                echo "District: " . $transaction->getShipping()->getAddress()->getDistrict() . '<br>';
                echo "City: " . $transaction->getShipping()->getAddress()->getCity() . '<br>';
                echo "State: " . $transaction->getShipping()->getAddress()->getState() . '<br>';
                echo "Country: " . $transaction->getShipping()->getAddress()->getCountry() . '<br>';
            }
            echo "Shipping type: " . $transaction->getShipping()->getType()->getTypeFromValue() . '<br>';
            echo "Shipping cost: " . $transaction->getShipping()->getCost() . '<br>';
        }
    }


	public static function smlToJson ($xmlString) 
	{

		$xmlString = str_replace(array("\n", "\r", "\t"), '', $xmlString);
		$xmlString = trim(str_replace('"', "'", $xmlString));
		$simpleXml = simplexml_load_string($xmlString);

		$json = json_encode($simpleXml);

		return $json;
	}

}

?>
