<?php

namespace Mdanter\Ecc\Math;

class MathAdapterFactory
{
    /**
     * @var GmpMathInterface
     */
    private static $forcedAdapter = null;

    /**
     * @param GmpMathInterface $adapter
     */
    public static function forceAdapter(GmpMathInterface $adapter = null)
    {
        self::$forcedAdapter = $adapter;
    }

    /**
     * @param bool $debug
     * @return DebugDecorator|GmpMathInterface|null
     */
    public static function getAdapter(bool $debug = false): GmpMathInterface
    {
        if (self::$forcedAdapter !== null) {
            return self::$forcedAdapter;
        }

        $adapter = new GmpMath();

        return self::wrapAdapter($adapter, $debug);
    }

    /**
     * @param GmpMathInterface $adapter
     * @param bool $debug
     * @return DebugDecorator|GmpMathInterface
     */
    private static function wrapAdapter(GmpMathInterface $adapter, bool $debug): GmpMathInterface
    {
        if ($debug === true) {
            return new DebugDecorator($adapter);
        }

        return $adapter;
    }
}
