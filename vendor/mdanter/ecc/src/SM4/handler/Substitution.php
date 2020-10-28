<?php
namespace Mdanter\Ecc\SM34\handler;
/**
 * Substitution @ SM3-PHP
 *
 * Code BY ch4o5
 * 10月. 14日 2019年
 * Powered by PhpStorm
 */

use Mdanter\Ecc\SM34\types\Word;
use Mdanter\Ecc\SM34\libs\WordConversion;

/**
 * 置换函数
 * Class Substitution
 *
 * @package SM3\handler
 */
class Substitution
{
    /** @var \SM3\types\Word 待置换的字 */
    private $X;
    
    /** @var array P0函数中两次左移的位数 */
    private $P0_shiftLeft_times = array(9, 17);
    /** @var array P1函数中两次左移的位数 */
    private $P1_shiftLeft_times = array(15, 23);
    
    /**
     * Substitution constructor.
     *
     * @param $X
     */
    public function __construct($X)
    {
        $this->X = $X;
    }
    
    /**
     * 压缩函数中的置换函数
     *
     * @return \SM3\types\Word  置换结果
     */
    public function P0()
    {
        return $this->substitutionFunction(0);
    }
    
    /**
     * 置换函数的公共函数
     *
     * @param $type
     *
     * @return \SM3\types\Word 置换结果
     */
    private function substitutionFunction($type)
    {
        if (!in_array($type, array(0, '0', 1, '1'))) {
            return new Word('');
        }
        
        $times_name = $type == 1
            ? $this->P1_shiftLeft_times
            : $this->P0_shiftLeft_times;
        
        $X_shiftLeft_1 = WordConversion::shiftLeftConversion($this->X, $times_name[0]);
        $X_shiftLeft_2 = WordConversion::shiftLeftConversion($this->X, $times_name[1]);
        
        return WordConversion::xorConversion(
            array(
                $this->X,
                $X_shiftLeft_1,
                $X_shiftLeft_2
            )
        );
    }
    
    /**
     * 消息扩展中的置换函数
     *
     * @return \SM3\types\Word 置换结果
     */
    public function P1()
    {
        return $this->substitutionFunction(1);
    }
}