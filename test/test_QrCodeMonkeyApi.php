<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/api/marketing/qrcodemonkey/QrCodeMonkeyManager.php';

$manager = QrCodeMonkeyManager::getInstance();

//$manager->createQrCodeForUrl('https://www.dreamdinners.com');



// Include the qrlib file
//include 'phplib/phpqrcode/qrlib.php';

include('phplib/phpqrcode/qrlib.php');

// $text variable has data for QR
$text = "GEEKS FOR GEEKS";

// QR Code generation using png()
// When this function has only the
// text parameter it directly
// outputs QR in the browser
//QRcode::png($text);


//QRcode::png(HTTPS_SERVER . '/item?recipe=1060',
//	"C:\\Development\\Sites\\qr.png",
//	'M');


//);
QRcode::png(HTTPS_SERVER . '/item?recipe=1060',"C:\\Development\\Sites\\qr.png",
    QR_ECLEVEL_L,
    3,
    4,
    false,
	0xFFFFFF,
0x666916
);
/*
Parameters: This function accepts five parameters as mentioned above and described below:

$text: This parameter gives the message which needs to be in QR code. It is mandatory parameter.
$file: It specifies the place to save the generated QR.
$ecc: This parameter specifies the error correction capability of QR. It has 4 levels L, M, Q and H.
$pixel_Size: This specifies the pixel size of QR.
$frame_Size: This specifies the size of Qr. It is from level 1-10.
*/