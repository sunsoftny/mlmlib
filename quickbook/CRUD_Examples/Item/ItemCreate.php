<?php
require "../../vendor/autoload.php";


use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Item;

// Prep Data Services
$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => "Q03UdLKqtZO72hHBHuqYQ0idDltDBwxXbw7YFkNeVAUqeA5PMS",
    'ClientSecret' => "LZH46ircXTO22Jh4UFvpc4fXq2eZnjU4io6AMwzM",
    'accessTokenKey' =>
    'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..0yJauFjMLz4STnJw9kT4wQ.QVzN6meY2uIHdq58o4knmMAAvHd-1tsGz5hNOJugIvGLD33sEm_vuqlRYwicWGbYLOyq-nKcQDsYJBPkhHUfg0_9ahiZMQ2LuRozLH6r_Y9qINsZ2-Hb45vaXKUYMrHfHD4OX8f44FPZ5jw0bp0Cs1DwxOfTCD5pwSTSDDR7LeKK83Vr3MP9qoPwr4mxRDy8zHKehxZAsAq5RKOyN6C6lPVIGAvroExMAuYRjSeNHAqjE8sJVzOuxs2OqeRGer3WzmYYyeUj0i_7Sl_NvxHNDyAdz7jFIidCTK672uaTgjK_VuhBQd0n1hExs2m5cAyhzijcxi5eCmtrzrD_HT9QdskFvcHMGq0Y3vb4IwXTVoZRJMfTWnOHNc6XxW_xah3K7gWTux-CUH7ByL0gMZPKy_Cq6vmfTYwyCGlzo89_6Ggv-PpmmRiDph8e7MTAcLdFBQi1GV3hxIW2vGsnxP9kx_ejER51ZxeLVtO87s6WaFaLf9UqGTgn4mS2P47KArXmer0CgaDkzpzzb8vZFF5qtZido0oqQX3Z76jZntEinSmMZgrVfWWyuK1M7z-cuD0KReZINBcFUmYxnaKSVGqB7hszIRJAMPEgJDrIfA7GYzglDpzuWqyPXvOVPdrbnzZ1wneOp0554QT4AuGCCLoYnaRrR_0m-oCmh-CPbqp4T7reJ3VoUgisoAJ8ftmazNEz.4BK-GYFp-nWSOe3qFqXqPA',
    'refreshTokenKey' => "Q011544274471Kn52K8fGxbWieP0X8gXuLfQXvLdGwCjfo11IV",
    'QBORealmID' => "123146096252439",
    'baseUrl' => "Development"
));
$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
$dataService->throwExceptionOnError(true);
$theResourceObj = Item::create([
  "Name" => "Inventory Supplier Sample",
  "UnitPrice" => 20,
  "IncomeAccountRef" => [
    "value" => "79",
    "name" => "Sales of Product Income"
  ],
  "ExpenseAccountRef" => [
    "value" => "80",
    "name" => "Cost of Goods Sold"
  ],
  "AssetAccountRef" => [
    "value" => "81",
    "name" => "Inventory Asset"
  ],
  "Type" => "Inventory",
  "TrackQtyOnHand" => true,
  "QtyOnHand" => 10,
  "InvStartDate" => "2015-01-01"
]);

$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
    $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
    echo $xmlBody . "\n";
}
