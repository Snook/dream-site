<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");

$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
$encoded_header = base64url_encode($header);


$payload = json_encode(['sub' => "1234567890", "given_name"=>"","family_name"=>"","email"=> "messi@awesomecompany.com","iat"=> "123422","nonce"=> "123422"]);
$encoded_payload = base64url_encode($payload);


$privateKeyFile = fopen("C:\\Development\\freshworks_API\\jwtRS256.key", "r") or die("Unable to open file!");

$privateKey = fread($privateKeyFile,filesize("C:\\Development\\freshworks_API\\jwtRS256.key"));
fclose($privateKeyFile);

$signature = hash_hmac('sha256', $encoded_header . "." . $encoded_payload, $privateKey, true);
$encoded_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

$data =  $encoded_header . '.' .$encoded_payload. '.' .$encoded_signature;

$redirectURL = "https://dreamdinners.freshworks.comsp/OIDC/315603540176320899/implicit?" . $data;


CApp::bounce($redirectURL);

function base64url_encode($string){
	$b64 = base64_encode($string);

	// Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
	if ($b64 === false) {
		return false;
	}

	// Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
	$url = strtr($b64, '+/', '-_');

	// Remove padding character from the end of line and return the Base64URL result
	return rtrim($url, '=');
}