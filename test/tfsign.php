<?php
require_once '../vendor/autoload.php';
use Rtgm\util\FormatSign;

$goodSign = 'MEUCIQDWveKrtx6VrosnYQHNBnRjolrlmi/mHwMWKU4bDxakQwIgfSX20s+Ci1SvFQBgx+kRMU3Z1xbHtT0kpZfAXVH8poc=';
$badSign  = 'MEYCIQDWveKrtx6VrosnYQHNBnRjolrlmi/mHwMWKU4bDxakQwIhAH0l9tLPgotUrxUAYMfpETFN2dcWx7U9JKWXwF1R/KaH';

$fs = new FormatSign();
$newSign = $fs->run($badSign);

echo $newSign."\n";

if ($newSign == $goodSign) {
    echo "OK";
} else {
    echo "Bad";
}