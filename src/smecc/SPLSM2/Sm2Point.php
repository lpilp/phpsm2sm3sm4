<?php
namespace Rtgm\smecc\SPLSM2;

/**
 * from https://github.com/lat751608899/sm2/blob/main/src/Point.php
 * @author zlq <751608899@qq.com>
 * modify by lpilp
 * point 类 ，使用zlq童鞋 修改至 ECC的 point 类 Mdanter\Ecc\Primitives\Point 
 */
class Sm2Point
{
    protected $eccParams;
    protected $x;
    protected $y;

    public function __construct(\GMP $x, \GMP $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->init();
    }

    protected function init()
    {

        $eccParams = Sm2Ecc::get_params();
        $this->eccParams=  $eccParams;
    }

    public function mul(\GMP $n, $isBase = true)
    {
        $zero = gmp_init(0, 10);
        $n = gmp_mod($n, $this->eccParams['p']);
        if (gmp_cmp($n, $zero) === 0) {
            return $this->getInfinity();
        }
        $p = $isBase ? new self($this->eccParams['gx'], $this->eccParams['gy']) : clone $this;
        /** @var Point[] $r */
        $r = [
            $this->getInfinity(), // Q
            $p// P
        ];
        $base = gmp_strval(gmp_init(gmp_strval($n), 10), 2);
        $n = strrev(str_pad($base, $this->eccParams['size'], '0', STR_PAD_LEFT));
        for ($i = 0; $i < $this->eccParams['size']; $i++) {
            $j = $n[$i];
            if($j == 1){
                $r[0] = $r[0]->add($r[1]); // r0 + r1 => p + 0 = p
            }
            $r[1] = $r[1]->getDouble();
        }
        $r[0]->checkOnLine();

        return $r[0];
    }

    public function add(Sm2Point $addend)
    {
        if ($addend->isInfinity()) {
            return clone $this;
        }

        if ($this->isInfinity()) { // 是否是无穷远点
            return clone $addend;
        }

        // x 相等
        if (gmp_cmp($addend->getX(), $this->x) === 0) {
            // y 也相等 = 倍点
            if (gmp_cmp($addend->getY(), $this->y) === 0) {
                return $this->getDouble();
            } else { // y 不相等 无穷远点
                return $this->getInfinity();
            }
        }

        $slope = $this->divMod(// λ = (y2 - y1) / (x2 - x1) (mod p)
            gmp_sub($addend->getY(), $this->y),  // y2 - y1
            gmp_sub($addend->getX(), $this->x)  // x2 - x1
        );
        // λ² - x1 - x2
        $xR =  $this->subMod(gmp_sub(gmp_pow($slope, 2), $this->x), $addend->getX());
        // (λ(x1 - x3)-y1)
        $yR = $this->subMod(gmp_mul($slope, gmp_sub($this->x, $xR)), $this->y);

        return new self($xR, $yR);
    }

    public function getDouble()
    {
        if ($this->isInfinity()) {
            return $this->getInfinity();
        }
        $threeX2 = gmp_mul(gmp_init(3, 10), gmp_pow($this->x, 2)); // 3x²
        $tangent = $this->divMod( // λ = (3x² + a) / 2y (mod p)
            gmp_add($threeX2, $this->eccParams['a']),  // 3x² + a
            gmp_mul(gmp_init(2, 10), $this->y)  // 2y
        );
        $x3 = $this->subMod(  // λ² - 2x (mod p)
            gmp_pow($tangent, 2), // λ²
            gmp_mul(gmp_init(2, 10), $this->x) // 2x
        );
        $y3 = $this->subMod( // λ(x - x3)-y  (mod p)
            gmp_mul($tangent, gmp_sub($this->x, $x3)), // λ(x - x3)
            $this->y
        );

        return new self($x3, $y3);
    }

    public function getInfinity()
    {
        return new self(gmp_init(0,10), gmp_init(0,10));
    }

    /**
     * @return \GMP
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return \GMP
     */
    public function getY()
    {
        return $this->y;
    }

    public function isInfinity()
    {
        return gmp_cmp($this->x, gmp_init(0,10)) === 0
            && gmp_cmp($this->y, gmp_init(0,10)) === 0;
    }

    /**
     * // k ≡ (x/y) (mod n) => ky ≡ x (mod n) => k y/x ≡ 1 (mod n)
     * @param $x
     * @param $y
     * @param null $n
     * @return \GMP|resource
     */
    protected function divMod($x, $y, $n = null)
    {
        $n = $n?:$this->eccParams['p'];
        // y k ≡ 1 (mod n) => k ≡ 1/y (mod n)
        $k = gmp_invert($y, $n);
        // kx ≡ x/y (mod n)
        $kx = gmp_mul($x, $k);

        return gmp_mod($kx, $n);
    }

    protected function subMod($x, $y, $n = null)
    {
       return gmp_mod(gmp_sub($x, $y), $n?:$this->eccParams['p']);
    }

    public function contains(\GMP $x, \GMP $y)
    {
        $eq_zero = gmp_cmp(
            $this->subMod(
                gmp_pow($y, 2),
                gmp_add(
                    gmp_add(
                        gmp_pow($x, 3),
                        gmp_mul($this->eccParams['a'], $x)
                    ),
                    $this->eccParams['b']
                )
            ),
            gmp_init(0, 10)
        );

        return $eq_zero;
    }

    public function checkOnLine()
    {
        if($this->contains($this->x, $this->y) !== 0){
            throw new \Exception('Invalid point');
        }

        return true;
    }
}