<?php
namespace Rtgm\smecc\SM4\handler;
use Rtgm\smecc\SM4\libs\WordConversion;
use Rtgm\smecc\SM4\types\Word;

/**
 * JHandler @ SM3-PHP
 *
 * Code BY ch4o5
 * 10月. 14th 2019
 * Powered by PhpStorm
 */



/**
 * j处理抽象类
 * Class JHandler
 *
 * @package SM3\instantiation
 */
abstract class JHandler
{
    /** @var string 常量T */
    protected $T = '';
    /** @var array j的长度区间 */
    protected $section_j = array();

    /**
     * JHandler constructor.
     *
     * @param $T        string 常量T
     * @param $smallest int j的最小可用值
     * @param $biggest  int j的最大可用值
     */
    public function __construct($T, $smallest, $biggest)
    {
        $this->setT($T);
        $this->setSectionJ($smallest, $biggest);
    }

    /**
     * 配置 继承本抽象类的子类可以处理的j的大小
     *
     * @param $smallest int j的最小长度
     * @param $biggest  int j的最大长度
     */
    public function setSectionJ($smallest, $biggest)
    {
        $this->section_j = array($smallest, $biggest);
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
    abstract public function FF($X, $Y, $Z);

    /**
     * 布尔函数
     *
     * @param $X
     * @param $Y
     * @param $Z
     *
     * @return mixed
     */
    abstract public function GG($X, $Y, $Z);

    /**
     * 读取常量T
     * @return Word
     * @throws \ErrorException
     */
    public function getT()
    {
        return new Word($this->T);
    }

    /**
     * 配置常量T
     *
     * @param string $T
     */
    public function setT($T)
    {
        $this->T = WordConversion::hex2bin($T);
    }
}
