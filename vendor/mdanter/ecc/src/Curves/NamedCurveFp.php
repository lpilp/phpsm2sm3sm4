<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Curves;

use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Primitives\CurveFp;
use Mdanter\Ecc\Primitives\CurveParameters;

class NamedCurveFp extends CurveFp
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string           $name
     * @param CurveParameters  $parameters
     * @param GmpMathInterface $adapter
     */
    public function __construct(string $name, CurveParameters $parameters, GmpMathInterface $adapter)
    {
        $this->name = $name;

        parent::__construct($parameters, $adapter);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
