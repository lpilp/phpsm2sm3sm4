<?php
$msg = '我爱你ILOVEYOU!';
echo openssl_digest($msg, 'sm3');