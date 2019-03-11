<?php
error_reporting(0);
/**
 * Copyright (c) 2011-2014 BITPAY, INC.
 *  
 * Bitcoin PHP payment library using the bitpay.com service. You can always 
 * download the latest version at https://github.com/bitpay/php-client
 * 
 * PHP Version 5
 * 
 * License: Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all 
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @category   Bitcoin
 * @package    Bitcoin\BitPay\PHP-Client-Library
 * @author     Rich Morgan <rich@bitpay.com>
 * @copyright  2014 BITPAY, INC.
 * @license    http://opensource.org/licenses/MIT  The MIT License (MIT)
 * @version    Release 1.9
 * @link       https://github.com/bitpay/php-client
 * @since      File available since Release 0.1
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
			WHERE  paymentsettings_default_name='bitcoin' AND paymentsettings_status='Active'"; 
			$results=$mysqli->query($sql);
			if($results->num_rows > 0) 
			{
						
				while($rows = $results->fetch_array(MYSQLI_ASSOC)) 
				{

					if($rows['paymentsettings_mode']=='sandbox')
					{
						$bpOptions['testnet'] = true;

					}
					else
					{
						$bpOptions['testnet'] = false;
					}

					$sqlcur="SELECT * FROM  ".PROMLM_PREFIX."sitesettings_table 
					WHERE  sitesettings_name='site_currency_code'"; 
					$resultscur=$mysqli->query($sqlcur);
					$row_cur= $resultscur->fetch_assoc();
					$currency=$row_cur['sitesettings_value'];

					$apikey=trim($rows['paymentsettings_accnum']);

					define('FIRSTKEY','565e9f7e01f8b232878c95cda75ea383f2a12d1a75ac4231=');
                    define('SECONDKEY','28da07e205beb4908feede366d3ee803d3ddaabf1ab16d45'); 


                    $first_key = base64_decode(FIRSTKEY);
                    $second_key = base64_decode(SECONDKEY);            
                    $mix = base64_decode($apikey);              
                    $method = "aes-256-cbc";    
                    $iv_length = openssl_cipher_iv_length($method);                 
                    $iv = substr($mix,0,$iv_length);
                    $second_encrypted = substr($mix,$iv_length,64);
                    $first_encrypted = substr($mix,$iv_length+64);                  
                    $data = openssl_decrypt($first_encrypted,$method,$first_key,OPENSSL_RAW_DATA,$iv);
                    $second_encrypted_new = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);         
                    if (hash_equals($second_encrypted,$second_encrypted_new))
                    $resdata= $data;

                
					$bpOptions['apiKey']=$data;
					
					$bpOptions['currency'] = $currency;


				}

			}
	}



/* 
 * Please look carefully through these options and adjust according to your installation.  
 * Alternatively, most of these options can be dynamically set upon calling the functions in bp_lib.
 */
  
/* 
 * REQUIRED!  This is the API key you created in your merchant account at bitpay.com
 * Example: $bpOptions['apiKey'] = 'L21K5IIUG3IN2J3';
 */

/*
 * Boolean value.  Whether to verify POS data by hashing above api key.  If set to false, you should
 * have some way of verifying that callback data comes from bitpay.com
 * Note: this option can only be changed here.  It cannot be set dynamically.
 */
$bpOptions['verifyPos'] = true;


/*
 * Optional - email where you want invoice update notifications sent
 */
$bpOptions['notificationEmail'] = '';


/*
 * Optional - url where bit-pay server should send payment notification updates.  See API doc for more details.
 * Example: $bpNotificationUrl = 'http://www.example.com/callback.php';
 */
$bpOptions['notificationURL'] = '';


/* 
 * Optional - url where the customer should be directed to after paying for the order
 * example: $bpNotificationUrl = 'http://www.example.com/confirmation.php';
 */
$bpOptions['redirectURL'] = '';


/*
 * REQUIRED!  This is the currency used for the price setting.  A list of other pricing
 * currencies supported is found at bitpay.com
 */



/* 
 * Boolean value.  Indicates whether anything is to be shipped with
 * the order (if false, the buyer will be informed that nothing is
 * to be shipped)
 */
$bpOptions['physical'] = true;


/*
 * If set to false, then notificaitions are only
 * sent when an invoice is confirmed (according the the
 * transactionSpeed setting). If set to true, then a notification
 * will be sent on every status change
 */
$bpOptions['fullNotifications'] = true;


/* 
 * REQUIRED! Transaction speed: low/medium/high.  See API docs for more details.
*/
$bpOptions['transactionSpeed'] = 'high'; 


/* 
 * Boolean value. Change to 'true' if you would like automatic logging of errors.
 * Otherwise you will have to call the bpLog function manually to log any information.
 */
$bpOptions['useLogging'] = false;


/* 
 * Boolean value. Change to 'true' if you want to use the testnet development environment at
 * test.bitpay.com. See: http://blog.bitpay.com/2014/05/13/introducing-the-bitpay-test-environment.html
 * for more information on using testnet.
 */

?>
