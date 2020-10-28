<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Exception;

use Throwable;

class UnsupportedCurveException extends \RuntimeException
{
    /**
     * @var null|string
     */
    private $oid;

    /**
     * @var null|string
     */
    private $curveName;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function setCurveName(string $curveName)
    {
        $this->curveName = $curveName;
        return $this;
    }

    public function setOid(string $oid)
    {
        $this->oid = $oid;
        return $this;
    }

    public function hasCurveName(): bool
    {
        return is_string($this->curveName);
    }

    public function hasOid(): bool
    {
        return is_string($this->oid);
    }

    public function getCurveName(): string
    {
        return $this->curveName;
    }

    public function getOid(): string
    {
        return $this->oid;
    }
}
