<?php
spl_autoload_register(function ($cls) {
    $map = array(
        'Mdanter\Ecc\Serializer\Util\CurveOidMapper' => __DIR__ .'/ecc/Serializer/Util/CurveOidMapper.php',
        'Mdanter\Ecc\Curves\CurveFactory'=>__DIR__ .'/ecc/Curves/CurveFactory.php',
    );
    

    if (isset($map[$cls])) {
        // echo $cls . ' loaded abc '.  $map[$cls].  PHP_EOL;
        include_once $map[$cls];
        return true;
    }
}, true, true);
