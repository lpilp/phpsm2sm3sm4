<?php
require_once '../vendor/autoload.php';
use Rtgm\smecc\SPLSM2\Sm2Asn1;

$str = 'MIGTAgEAMBMGByqGSM49AgEGCCqBHM9VAYItBHkwdwIBAQQgHaEvjmM9ZMt0xCHT
Y65RBRkWxY9bBfl/Fag0bvP1r9OgCgYIKoEcz1UBgi2hRANCAATQeZSDbPzUA57d
UZTQBjdiY36CNk6ecsEuMvG3XpNxoJzome32RDEUkDc/qihPAmHaK48SCuVaoG5B
Hk+QBDaJ';



// step 1
$array1 = Sm2Asn1::decode(base64_decode($str));
// print_r($array1);
//  解出 
// Array
// (
//     [0] => Array
//         (
//             [0] => 00
//             [1] => Array
//                 (
//                     [0] => 2a8648ce3d0201
//                     [1] => 2a811ccf5501822d
//                 )

//             [2] => 307702010104201da12f8e633d64cb74c421d363ae51051916c58f5b05f97f15a8346ef3f5afd3a00a06082a811ccf5501822da14403420004d07994836cfcd4039edd5194d0063762637e82364e9e72c12e32f1b75e9371a09ce899edf644311490373faa284f0261da2b8f120ae55aa06e411e4f90043689
//         )

// )
// step 2:

$array2 = Sm2Asn1::decode(hex2bin($array1[0][2]));
// print_r($array2);
echo "私钥： ".$array2[0][1]."\n";
// (
//     [0] => Array
//         (
//             [0] => 01
//             [1] => 1da12f8e633d64cb74c421d363ae51051916c58f5b05f97f15a8346ef3f5afd3
//             [2] => 06082a811ccf5501822d
//             [3] => 03420004d07994836cfcd4039edd5194d0063762637e82364e9e72c12e32f1b75e9371a09ce899edf644311490373faa284f0261da2b8f120ae55aa06e411e4f90043689   
//         )

// )

$array3 = Sm2Asn1::decode(hex2bin($array2[0][3]));
// print_r($array3);
echo "私钥： ".$array3[0][1]."\n";





