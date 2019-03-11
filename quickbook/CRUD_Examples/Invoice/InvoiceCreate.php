<?php

$quickbook_clientid = $_POST['quickbook_clientid'];
$quickbook_clientsecretid = $_POST['quickbook_clientsecretid'];
$quickbook_refreshtokenkey = $_POST['quickbook_refreshtokenkey'];
$quickbook_qborealmid = $_POST['quickbook_qborealmid'];
$qb_customerid = $_POST['qb_customerid'];
$amount = $_POST['amount'];

/*$quickbook_clientid = "Q0Hu9BtKkLFvWN9tleE0F5s9i05mOuRkQf2osU4PaLGNlkn4OJ";
$quickbook_clientsecretid = "xUJFswNh8iPq4A2al0FvurK8M0kZmSYqLlKgfV5f";
$quickbook_refreshtokenkey = "L011548338586tRM2ICVAV7vI7AqrXT5xSlLN1uYaUX5f78U4l";
$quickbook_qborealmid = "123146109177164";
$qb_customerid = 15;
$amount = "50";*/

$authcode = $quickbook_clientid.":".$quickbook_clientsecretid;
$authorization = "Basic ".base64_encode($authcode);

require "../../vendor/autoload.php";
$url = "https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer";
$post_fields = 'grant_type=refresh_token&refresh_token='.$quickbook_refreshtokenkey;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$post_fields);  //Post Fields
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$headers = [
    'Accept: application/json', 
    'Authorization: '.$authorization,
    'Content-Type: application/x-www-form-urlencoded',
    'Host: oauth.platform.intuit.com',
    'Cache-Control: no-cache'];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$server_output = json_decode(curl_exec ($ch));
curl_close ($ch);
$quickbook_refreshtokenkey = $server_output->refresh_token;
$quickbook_accesstokenkey = $server_output->access_token;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
// Prep Data Services
$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $quickbook_clientid,
    'ClientSecret' => $quickbook_clientsecretid,
    'accessTokenKey' =>$quickbook_accesstokenkey,
    'refreshTokenKey' => $quickbook_refreshtokenkey,
    'QBORealmID' => $quickbook_qborealmid,
    'baseUrl' => "Production"
));
$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
$dataService->throwExceptionOnError(true);
//Add a new Invoice
$theResourceObj = Invoice::create([
    "Line" => [
    [
         "Amount" => $amount,
         "DetailType" => "SalesItemLineDetail",
         "SalesItemLineDetail" => [
           "ItemRef" => [
             "value" => 1,
             "name" => "Services"
           ]
         ]
    ]
    ],
    "CustomerRef"=> [
          "value"=> $qb_customerid
    ]
]);
$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
}else {
    echo $resultingObj->Id;    
}
