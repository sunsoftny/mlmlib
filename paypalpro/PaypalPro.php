<?php

/**
 * PaypalPro Class
 * Helps to make credit card payment by PayPal Payments Pro
 * 
 * Author: CodexWorld
 * Author Email: contact@codexworld.com
 * Author URL: http://www.codexworld.com
 * Tutorial URL: http://www.codexworld.com/paypal-pro-payment-gateway-integration-in-php/
 */

global $bpOptions;
    define("PROMLM_PREFIX",'promlm_');

    $re=file_get_contents('Bin/Configuration.php');
    $res=explode('\'',$re);
    

     $hostname=$res[1];

     $username=$res[3];

     $password=$res[5];

     $dbname  =$res[7];

    $mysqli = new mysqli($hostname, $username, $password, $dbname);
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    if($mysqli) 
    {
            
            $sql="SELECT * FROM  ".PROMLM_PREFIX."paymentsettings_table 
            WHERE  paymentsettings_default_name='paypalpro' AND paymentsettings_status='Active'"; 
            $results=$mysqli->query($sql);
            if($results->num_rows > 0) 
            {
                        
                while($rows = $results->fetch_array(MYSQLI_ASSOC)) 
                {

                    $payout_apivalues=$rows['payout_apivalues'];
                    $paymentsettings_mode=$rows['paymentsettings_mode'];
                    $api=json_decode($payout_apivalues);
                    $paypal_password=$api->paypal_password;
                    $paypal_signature=$api->paypal_signature;

                    define('FIRSTKEY','565e9f7e01f8b232878c95cda75ea383f2a12d1a75ac4231=');
                    define('SECONDKEY','28da07e205beb4908feede366d3ee803d3ddaabf1ab16d45'); 


                    $first_key = base64_decode(FIRSTKEY);
                    $second_key = base64_decode(SECONDKEY);            
                    $mix = base64_decode($paypal_password);              
                    $method = "aes-256-cbc";    
                    $iv_length = openssl_cipher_iv_length($method);                 
                    $iv = substr($mix,0,$iv_length);
                    $second_encrypted = substr($mix,$iv_length,64);
                    $first_encrypted = substr($mix,$iv_length+64);                  
                    $data = openssl_decrypt($first_encrypted,$method,$first_key,OPENSSL_RAW_DATA,$iv);
                    $second_encrypted_new = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);         
                    if (hash_equals($second_encrypted,$second_encrypted_new))
                    $resdata= $data;


                    $first_key = base64_decode(FIRSTKEY);
                    $second_key = base64_decode(SECONDKEY);            
                    $mix = base64_decode($paypal_signature);              
                    $method = "aes-256-cbc";    
                    $iv_length = openssl_cipher_iv_length($method);                 
                    $iv = substr($mix,0,$iv_length);
                    $second_encrypted = substr($mix,$iv_length,64);
                    $first_encrypted = substr($mix,$iv_length+64);                  
                    $datasign = openssl_decrypt($first_encrypted,$method,$first_key,OPENSSL_RAW_DATA,$iv);
                    $second_encrypted_new = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);         
                    if (hash_equals($second_encrypted,$second_encrypted_new))
                    $resdata= $datasign;

                    $_SESSION['paypal_apiusername'] = trim($api->paypal_username);
                    $_SESSION['paypal_apipwd'] = trim($data);
                    $_SESSION['paypal_apisignature']=trim($datasign);
                    $_SESSION['paypal_mode']=$paymentsettings_mode;

                }

            }
    }


class PaypalPro
{


    //Configuration Options
    var $apiUsername ='';
    var $apiPassword = '';
    var $apiSignature = '';
   //  var $apiEndpoint = 'https://api-3t.paypal.com/nvp';//For Online
    var $apiEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
    var $subject = '';
    var $authToken = '';
    var $authSignature = '';
    var $authTimestamp = '';
    var $useProxy = FALSE;
    var $proxyHost = '127.0.0.1';
    var $proxyPort = 808;
    var $paypalURL = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
  //var $paypalURL = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';//For Online
    var $version = '65.1';
  //  var $version = '51.0';
    var $ackSuccess = 'SUCCESS';
    var $ackSuccessWarning = 'SUCCESSWITHWARNING';
    
    public function __construct($config = array()){ 
        ob_start();
        session_start();
        if (count($config) > 0){
            foreach ($config as $key => $val){
                if (isset($key) && $key == 'live' && $val == 1){
                    $this->paypalURL = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
                }else if (isset($this->$key)){
                    $this->$key = $val;
                }
            }
        }
        if($_SESSION['paypal_mode']=='sandbox')
    {
         $apiEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';

         $paypalURL = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
    }
     if($_SESSION['paypal_mode']=='live')
    {
             $apiEndpoint = 'https://api-3t.paypal.com/nvp';

             $paypalURL = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
    }
    }
    public function nvpHeader(){

        $nvpHeaderStr = "";

         $this->apiUsername= $_SESSION['paypal_apiusername'];
         $this->apiPassword = $_SESSION['paypal_apipwd'];
         $this->apiSignature = $_SESSION['paypal_apisignature'];

         
        if((!empty($this->apiUsername)) && (!empty($this->apiPassword)) && (!empty($this->apiSignature)) && (!empty($subject))) {
            $authMode = "THIRDPARTY";
        }else if((!empty($this->apiUsername)) && (!empty($this->apiPassword)) && (!empty($this->apiSignature))) {
            $authMode = "3TOKEN";
        }elseif (!empty($this->authToken) && !empty($this->authSignature) && !empty($this->authTimestamp)) {
            $authMode = "PERMISSION";
        }elseif(!empty($subject)) {
            $authMode = "FIRSTPARTY";
        }
        
        switch($authMode) {
            case "3TOKEN" : 
                $nvpHeaderStr = "&PWD=".urlencode($this->apiPassword)."&USER=".urlencode($this->apiUsername)."&SIGNATURE=".urlencode($this->apiSignature);
                break;
            case "FIRSTPARTY" :
                $nvpHeaderStr = "&SUBJECT=".urlencode($this->subject);
                break;
            case "THIRDPARTY" :
                $nvpHeaderStr = "&PWD=".urlencode($this->apiPassword)."&USER=".urlencode($this->apiUsername)."&SIGNATURE=".urlencode($this->apiSignature)."&SUBJECT=".urlencode($subject);
                break;		
            case "PERMISSION" :
                $nvpHeaderStr = $this->formAutorization($this->authToken,$this->authSignature,$this->authTimestamp);
                break;
        }
        return $nvpHeaderStr;
    }
    
    /**
      * hashCall: Function to perform the API call to PayPal using API signature
      * @methodName is name of API  method.
      * @nvpStr is nvp string.
      * returns an associtive array containing the response from the server.
    */
    public function hashCall($methodName,$nvpStr){
        // form header string
        $nvpheader = $this->nvpHeader();

        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->apiEndpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
    
        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        //in case of permission APIs send headers as HTTPheders
        if(!empty($this->authToken) && !empty($this->authSignature) && !empty($this->authTimestamp))
         {
            $headers_array[] = "X-PP-AUTHORIZATION: ".$nvpheader;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
            curl_setopt($ch, CURLOPT_HEADER, false);
        }
        else 
        {
            $nvpStr = $nvpheader.$nvpStr;
        }
        //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
       //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
        if($this->useProxy)
            curl_setopt ($ch, CURLOPT_PROXY, $this->proxyHost.":".$this->proxyPort); 
    
        //check if version is included in $nvpStr else include the version.
        if(strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
            $nvpStr = "&VERSION=" . urlencode($this->version) . $nvpStr;	
        }
        
        $nvpreq="METHOD=".urlencode($methodName).$nvpStr;
        //setting the nvpreq as POST FIELD to curl
        curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);
    
        //getting response from server
        $response = curl_exec($ch);
        
        //convrting NVPResponse to an Associative Array
        $nvpResArray = $this->deformatNVP($response);
        $nvpReqArray = $this->deformatNVP($nvpreq);
        $_SESSION['nvpReqArray']=$nvpReqArray;
    
        if (curl_errno($ch)) {
            die("CURL send a error during perform operation: ".curl_error($ch));
        } else {
            //closing the curl
            curl_close($ch);
        }
    
        return $nvpResArray;
    }
    
    /** This function will take NVPString and convert it to an Associative Array and it will decode the response.
     * It is usefull to search for a particular key and displaying arrays.
     * @nvpstr is NVPString.
     * @nvpArray is Associative Array.
     */
    public function deformatNVP($nvpstr){
        $intial=0;
        $nvpArray = array();
    
        while(strlen($nvpstr)){
            //postion of Key
            $keypos = strpos($nvpstr,'=');
            //position of value
            $valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
    
            /*getting the Key and Value values and storing in a Associative Array*/
            $keyval = substr($nvpstr,$intial,$keypos);
            $valval = substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] =urldecode( $valval);
            $nvpstr = substr($nvpstr,$valuepos+1,strlen($nvpstr));
         }
        return $nvpArray;
    }
    
    public function formAutorization($auth_token,$auth_signature,$auth_timestamp){
        $authString="token=".$auth_token.",signature=".$auth_signature.",timestamp=".$auth_timestamp ;
        return $authString;
    }
    
    public function paypalCall($params){
        /*
         * Construct the request string that will be sent to PayPal.
         * The variable $nvpstr contains all the variables and is a
         * name value pair string with & as a delimiter
         */
		$recurringStr = (array_key_exists("recurring",$params) && $params['recurring'] == 'Y')?'&RECURRING=Y':'';
        $nvpstr = "&PAYMENTACTION=".$params['paymentAction']."&AMT=".$params['amount']."&CREDITCARDTYPE=".$params['creditCardType']."&ACCT=".$params['creditCardNumber']."&EXPDATE=".$params['expMonth'].$params['expYear']."&CVV2=".$params['cvv']."&FIRSTNAME=".$params['firstName']."&LASTNAME=".$params['firstName']."&CITY=".$params['city']."&ZIP=".$params['zip']."&COUNTRYCODE=".$params['countryCode']."&CURRENCYCODE=".$params['currencyCode'].$recurringStr;
    
        /* Make the API call to PayPal, using API signature.
           The API response is stored in an associative array called $resArray */
        $resArray = $this->hashCall("DoDirectPayment",$nvpstr); 


    
        return $resArray;
    }

    
}
?>