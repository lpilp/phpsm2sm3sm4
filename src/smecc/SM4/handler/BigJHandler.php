<?php
namespace Rtgm\smecc\SM4\handler;
use Rtgm\smecc\SM4\libs\WordConversion;

/**
 * BigJHandler @ SM3-PHP
 *
 * Code BY ch4o5
 * 10月. 14日 2019年
 * Powered by PhpStorm
 */


/**
 * 小j处理类
 * Class BigJHandler
 *
 * @package SM3\handler
 */
class BigJHandler extends JHandler
{
    /** @var int j的最大可用值 */
    const SMALLEST_J = 16;
    /** @var int j的最小可用值 */
    const BIGGEST_J = 63;
    /** @var string T常量 */
    const T = '7a879d8a';

    /**
     * 补充父类
     * SmallJHandler constructor.
     */
    public function __construct()
    {
        parent::__construct(self::T, self::SMALLEST_J, self::BIGGEST_J);
    }

    /**
     * 布尔函数
     *
     * @param $X string 长度32的比特串
     * @param $Y string
     * @param $Z string
     *
     * @return mixed
     */
    public function FF($X, $Y, $Z)
    {
        $X_and_Y = WordConversion::andConversion(array($X, $Y));
        $X_and_Z = WordConversion::andConversion(array($X, $Z));
        $Y_and_Z = WordConversion::andConversion(array($Y, $Z));

        return WordConversion::orConversion(
            array(
                $X_and_Y,
                $X_and_Z,
                $Y_and_Z
            )
        );
    }

    /**
     * 布尔函数
     *
     * @param $X
     * @param $Y
     * @param $Z
     *
     * @return mixed
     */
    public function GG($X, $Y, $Z)
    {
        $X_and_Y = WordConversion::andConversion(array($X, $Y));

        $not_X = WordConversion::notConversion($X);
        $not_X_and_Z = WordConversion::andConversion(array($not_X, $Z));

        return WordConversion::orConversion(array($X_and_Y, $not_X_and_Z));
    }
}
