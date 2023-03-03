<?php
require_once '../vendor/autoload.php';
use Rtgm\util\MyAsn1;


print_r(MyAsn1::decode_file('./data/rsa.pem'));

$data = 'MHcCAQEEIC3X4bpf0xxL1EKlmbFN07/dPgIlC5S0jFinMA3GEmAdoAoGCCqBHM9VAYItoUQDQgAEm7RF3E+Fv9BY9AEgKUzWzxx0yuZYfJn6EZ4HIZrbPnt/yOYLsJSax2CuWtREbNS31tDRPOGPqHh3DO1FyQwIYw==';
print_r(MyAsn1::decode($data ,'base64'));