<?php
namespace Rtgm\smecc\SM4\types;
use ErrorException;

/**
 * BitString @ SM3-PHP
 *
 * Code BY ch4o5
 * 10月. 14th 2019
 * Powered by PhpStorm
 */

/**
 * 比特串
 * 由0和1组成的二进制数字序列。
 * Class BitString
 *
 * @package SM3\types
 */
class BitString implements \ArrayAccess
{
    /** @var string 一个比特串类型的变量 */
    protected $bit_string = '';

    /**
     * BitString constructor.
     *
     * @param            $string        string|BitString|Word|mixed 传入的数据
     * @param false|null $is_bit_string 是否比特串（只有入口明确知道不是）
     *
     * @throws \ErrorException
     */
    public function __construct($string, $is_bit_string = null)
    {
        if (is_object($string)) {
            $string = $string->getString();
        }

        if ($is_bit_string === false) {
            // 指定不是比特串的，直接转换
            $this->bit_string = "{$this->str2bin($string)}";
        } else {
            // 默认走个验证试试
            $this->bit_string = $this->is_bit_string($string)
                ? $string
                : "{$this->str2bin($string)}";
        }
    }

    /**
     * 字符串转比特串
     *
     * @param $str int|string 普通字符串
     *
     * @return string   转换为比特串
     * @throws \ErrorException
     */
    private function str2bin($str)
    {
        if (!is_string($str) && !is_int($str)) {
            throw new ErrorException('输入的类型错误');
        }
        if (is_int($str)) {
            return decbin($str);
        }
        $fileType = mb_detect_encoding($str , array('UTF-8','GBK','LATIN1','BIG5')) ;
        if( $fileType != 'UTF-8'){
            $str = mb_convert_encoding($str ,'utf-8' , $fileType);
        }
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        foreach ($arr as &$v) {
            $fileType = mb_detect_encoding($v , array('UTF-8','GBK','LATIN1','BIG5')) ;
            /* if( $fileType != 'GBK'){
                $v = mb_convert_encoding($v ,'GBK' , $fileType); //这里也要utf8啊，
            } */
            if( $fileType != 'UTF-8'){
                $str = mb_convert_encoding($str ,'utf-8' , $fileType);
            }
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);

            while (strlen($v) < 8) {
                $v = '0' . $v;
            }
            unset($temp);
        }
        return join('', $arr);
    }

    /**
     * 判断是否为比特串类型
     *
     * @param string|Word $string
     *
     * @return bool
     */
    public function is_bit_string($string)
    {
        if (is_object($string)) {
            $string = $string->getString();
        }
        // 检查是否为字符串
        if (!is_string($string)) {
            return false;
        }

        // 检查是否为只有0和1组成的字符串
        $array = array_filter(str_split($string));
        foreach ($array as $value) {
            if (!in_array(
                $value,
                array(
                    0,
                    '0',
                    1,
                    '1'
                ),
                true
            )) {
                return false;
            }
        }

        return true;
    }

    public function __toString()
    {
        return $this->getString();
    }

    /**
     * 获取比特串的值
     *
     * @return string
     */
    public function getString()
    {
        return $this->bit_string;
    }

    public function offsetGet($offset)
    {
        return $this->bit_string[$offset];
    }

    /**
     * Whether a offset exists
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->bit_string[$offset]);
    }

    /**
     * Offset to set
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return BitString
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->bit_string[$offset] = $value;
        return $this;
    }

    /**
     * Offset to unset
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->bit_string[$offset]);
    }
}
