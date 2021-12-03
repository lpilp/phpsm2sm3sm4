<?php
namespace Rtgm\smecc\SM4\handler;
/**
 * ExtendedCompression @ SM3-PHP
 *
 * Code BY ch4o5
 * 10月. 15日 2019年
 * Powered by PhpStorm
 */

use Rtgm\smecc\SM4\libs\WordConversion;
use Rtgm\smecc\SM4\types\Word;


/**
 * 扩展压缩算法
 * Class ExtendedCompression
 *
 * @package SM3\handler
 */
class ExtendedCompression
{
    /** @var array $W */
    private $W;
    /** @var array $W ' */
    private $W_s;

    /**
     * 压缩函数
     *
     * @param $Vi
     * @param $Bi
     *
     * @return Word
     * @throws \ErrorException
     */
    public function CF($Vi, $Bi)
    {
        // 消息扩展
        $this->extended($Bi);

        /** @var array $registers 八个寄存器的名字 */
        $registers = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H'
        );

        // 将 Vi 的值依次放入八个寄存器中
        // 下列注释用于防止IDE报错
        /** @var Word $A */
        /** @var Word $B */
        /** @var Word $C */
        /** @var Word $D */
        /** @var Word $E */
        /** @var Word $F */
        /** @var Word $G */
        /** @var Word $H */
        foreach ($registers as $i => $register) {
            $$register = new Word(substr($Vi, $i * 32, 32));
        }


        $small_j_handler = new SmallJHandler();
        $big_j_handler = new BigJHandler();

        for ($j = 0; $j < 64; $j++) {
            $j_handler = ($j >= SmallJHandler::SMALLEST_J && $j < BigJHandler::SMALLEST_J)
                ? $small_j_handler
                : $big_j_handler;

            $SS1 = WordConversion::shiftLeftConversion(
                WordConversion::addConversion(
                    array(
                        WordConversion::shiftLeftConversion($A, 12),
                        $E,
                        WordConversion::shiftLeftConversion($j_handler->getT(), $j)
                    )
                ),
                7
            );

            $SS2 = WordConversion::xorConversion(
                array(
                    $SS1,
                    WordConversion::shiftLeftConversion($A, 12)
                )
            );

            $TT1 = WordConversion::addConversion(
                array(
                    $j_handler->FF($A, $B, $C),
                    $D,
                    $SS2,
                    $this->W_s[$j]
                )
            );

            $TT2 = WordConversion::addConversion(
                array(
                    $j_handler->GG($E, $F, $G),
                    $H,
                    $SS1,
                    $this->W[$j]
                )
            );

            $D = $C;

            $C = WordConversion::shiftLeftConversion($B, 9);

            $B = $A;

            $A = $TT1;

            $H = $G;

            $G = WordConversion::shiftLeftConversion($F, 19);

            $F = $E;

            $TT2_object = new Substitution($TT2);
            $E = $TT2_object->P0();
        }

        return WordConversion::xorConversion(
            array(
                join(
                    '',
                    array(
                        (new Word($A)),
                        (new Word($B)),
                        (new Word($C)),
                        (new Word($D)),
                        (new Word($E)),
                        (new Word($F)),
                        (new Word($G)),
                        (new Word($H))
                    )
                ),
                $Vi
            )
        );
    }

    /**
     * 消息扩展
     *
     * 将消息分组B(i)按以下方法扩展生成132个字W0, W1, · · · , W67, W′0, W′1, · · · , W′63，
     * 用于压缩函数CF
     *
     * @param  $Bi 消息分组中的第i个，最大512位 (\SM3\types\BitString)
     *
     * @return void
     * @throws \ErrorException
     */
    public function extended($Bi)
    {
        // 将消息分组B(i)划分为16个字W0, W1, · · · , W15。
        $this->W = $this->W_s = array();

        $word_per_times = (int)ceil(strlen($Bi) / 16);
        for ($i = 0; $i < 16; $i++) {
            $this->W[$i] = new Word(
                substr($Bi, $i * $word_per_times, $word_per_times)
            );
        }

        // 计算W
        for ($j = 16; $j <= 67; $j++) {
            $param_1 = (new Substitution(
                WordConversion::xorConversion(
                    array(
                        $this->W[$j - 16],
                        $this->W[$j - 9],
                        WordConversion::shiftLeftConversion($this->W[$j - 3], 15)
                    )
                )
            ));

            $this->W[$j] = WordConversion::xorConversion(
                array(
                    $param_1->P1(),
                    WordConversion::shiftLeftConversion($this->W[$j - 13], 7),
                    $this->W[$j - 6]
                )
            );
        }

        unset($j);

        // 计算W'
        for ($j = 0; $j <= 63; $j++) {
            $this->W_s[$j] = WordConversion::xorConversion(
                array(
                    $this->W[$j],
                    $this->W[$j + 4]
                )
            );
        }
    }
}
