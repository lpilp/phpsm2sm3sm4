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
echo "\n=========\n";
//good 补0，
$goodSign2 = "MEMCHx7T5iZF+kfk0mNDxVOX2ZOytWjcFBDCRMyUZsvdk\/8CICOZz0A91TlSbZWAhs8J24nWT35l1Su8zegr+vomI9P+";
echo (bin2hex(base64_decode($goodSign2)));
echo "\n";
$newSign2 = $fs->format_cmbc($goodSign2);
echo (bin2hex(base64_decode($newSign2)));