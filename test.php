<?php
$userId = "12131241";
$id=unpack("C*",$userId);
$len = sizeof($id)*8;
echo $len;