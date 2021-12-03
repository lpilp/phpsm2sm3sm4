<?php
namespace Rtgm\smecc\SM4\types;


/**
 * 字类型
 * 长度32的比特串
 * Class Word
 *
 * @package SM3\types\
 */
class Word extends BitString implements \ArrayAccess
{
    /** @var int 设置长度为32 */
    const length = 32;
    /** @var string */
    private $word = '';

    /**
     * Word constructor.
     *
     * @param $string
     *
     * @throws \ErrorException
     */
    public function __construct($string)
    {
        parent::__construct($string);

        if (strlen($this->bit_string) === self::length) {
            $this->word = $this->bit_string;
        } else {
            $this->word = intval($this->bit_string) === 0
                ? 0
                : $this->bit_string;

            if (strlen($this->word) <= self::length) {
                $this->word = str_pad(
                    $this->word,
                    self::length,
                    '0',
                    STR_PAD_LEFT
                );
            } else {
                $this->word = substr($this->bit_string, -(self::length));
            }
        }
    }

    public function __toString()
    {
        return $this->word;
    }

    public function offsetGet($offset)
    {
        return $this->word[$offset];
    }

    public function getString()
    {
        return $this->word;
    }

    /**
     * @return string
     */
    public function getBitString()
    {
        return $this->bit_string;
    }

}
