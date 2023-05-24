<?php
require_once '../vendor/autoload.php';
use Rtgm\smecc\SPLSM2\SimpleSm2;

$publicKey = '04eb4b8bbe15e3ad94b85196adc2c6f694436b3c1336170fd1daac8b10d2b8824ada9687c138fb81590e0f66ab9678161732ac0d7866b169e76b74483285f2bc04';
$privateKey = '0bc1c1d2771b64ba1922d72f8a451cd09a82176f74d975d484ec62c862176b75';
$userId = '1234567812345678';
$document = "app_id=test221124213123300000012";


$ssm2 = new SimpleSm2($privateKey,$publicKey);


list($r, $s) = $ssm2->sign_raw($document, $privateKey, $publicKey,$userId);

var_dump(gmp_strval($r,16), gmp_strval($s,16));