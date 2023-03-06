<?php
require_once '../vendor/autoload.php';
use Rtgm\util\MyAsn1;


print_r(MyAsn1::decode_file('./data/sm2.pem'));

$data = 'MHcCAQEEIDMLq58c/Ox37b0NA4Ok65BcRRG+OmF1O+LtAIwRvmm8oAoGCCqBHM9V
AYItoUQDQgAEyqo4GGHqDU6XIBpDCzEfi7Z2EpUzmU/s46pJioQkd7tNYAb3Em2J
JJRFMK4l6WPlGze3zC66NaRZuyBagjDiVQ==';
print_r(MyAsn1::decode($data ,'base64')); 
