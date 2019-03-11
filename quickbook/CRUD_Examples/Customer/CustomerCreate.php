<?php


$quickbook_clientid = $_POST['quickbook_clientid'];
$quickbook_clientsecretid = $_POST['quickbook_clientsecretid'];
$quickbook_refreshtokenkey = $_POST['quickbook_refreshtokenkey'];
$quickbook_qborealmid = $_POST['quickbook_qborealmid'];
$members_username = $_POST['members_username'];
$members_email = $_POST['members_email'];
$members_firstname = $_POST['members_firstname'];
$members_lastname = $_POST['members_lastname'];
$members_state = $_POST['members_state'];
$members_city = $_POST['members_city'];
$members_address = $_POST['members_address'];
$members_phone = $_POST['members_phone'];
$members_zip = $_POST['members_zip'];
$members_country = $_POST['members_country'];

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
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    
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
use QuickBooksOnline\API\Facades\Customer;

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


//Add a new Vendor
$theResourceObj = Customer::create([
    "BillAddr" => [
        "Line1" => $members_address,
        "City" => $members_city,
        "Country" => $members_country,        
        "PostalCode" => $members_zip
    ],
    "Notes" => "Here are other details.",
    "Title" => "Mr",
    "GivenName" => $members_username,
    "MiddleName" => $members_firstname,
    "FamilyName" => $members_firstname,
    "Suffix" => $members_lastname,    
    "CompanyName" => $members_username,
    "DisplayName" => $members_username,
    "PrimaryPhone" => [
        "FreeFormNumber" => $members_phone
    ],
    "PrimaryEmailAddr" => [
        "Address" => $members_email
    ]
]);



$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
}else {        
    echo $resultingObj->Id;    
}
