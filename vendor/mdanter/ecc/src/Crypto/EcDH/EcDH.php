<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Crypto\EcDH;

/**
 * *********************************************************************
 * Copyright (C) 2012 Matyas Danter
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
 * OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * ***********************************************************************
 */

use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Exception\ExchangeException;
use Mdanter\Ecc\Math\GmpMathInterface;

/**
 * This class is the implementation of ECDH.
 * EcDH is safe key exchange and achieves
 * that a key is transported securely between two parties.
 * The key then can be hashed and used as a basis in
 * a dual encryption scheme, along with AES for faster
 * two- way encryption.
 */
class EcDH implements EcDHInterface
{
    /**
     * Adapter used for math calculations
     *
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     * Secret key between the two parties
     *
     * @var PublicKeyInterface
     */
    private $secretKey = null;

    /**
     *
     * @var PublicKeyInterface
     */
    private $recipientKey;

    /**
     *
     * @var PrivateKeyInterface
     */
    private $senderKey;

    /**
     * Initialize a new exchange from a generator point.
     *
     * @param GmpMathInterface $adapter A math adapter instance.
     */
    public function __construct(GmpMathInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Crypto\EcDH\EcDHInterface::calculateSharedKey()
     */
    public function calculateSharedKey(): \GMP
    {
        $this->calculateKey();

        return $this->secretKey->getPoint()->getX();
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Crypto\EcDH\EcDHInterface::createMultiPartyKey()
     */
    public function createMultiPartyKey(): PublicKeyInterface
    {
        $this->calculateKey();

        return $this->secretKey;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Crypto\EcDH\EcDHInterface::setRecipientKey()
     */
    public function setRecipientKey(PublicKeyInterface $key = null)
    {
        $this->recipientKey = $key;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Crypto\EcDH\EcDHInterface::setSenderKey()
     */
    public function setSenderKey(PrivateKeyInterface $key)
    {
        $this->senderKey = $key;
        return $this;
    }

    /**
     *
     */
    private function calculateKey()
    {
        $this->checkExchangeState();

        if ($this->secretKey === null) {
            try {
                // Multiply our secret with recipients public key
                $point = $this->recipientKey->getPoint()->mul($this->senderKey->getSecret());

                // Ensure we completed a valid exchange, ensure we can create a
                // public key instance for the shared secret using our generator.
                $this->secretKey = $this->senderKey->getPoint()->getPublicKeyFrom($point->getX(), $point->getY());
            } catch (\Exception $e) {
                throw new ExchangeException("Invalid ECDH exchange", 0, $e);
            }
        }
    }

    /**
     * Verifies that the shared secret is known, or that the required keys are available
     * to calculate the shared secret.
     * @throws \RuntimeException when the exchange has not been made.
     */
    private function checkExchangeState()
    {
        if ($this->secretKey !== null) {
            return;
        }

        if ($this->senderKey === null) {
            throw new ExchangeException('Sender key not set.');
        }

        if ($this->recipientKey === null) {
            throw new ExchangeException('Recipient key not set.');
        }

        // Check the point exists on our curve.
        $point = $this->recipientKey->getPoint();
        if (!$this->senderKey->getPoint()->getCurve()->contains($point->getX(), $point->getY())) {
            throw new ExchangeException("Invalid ECDH exchange - Point does not exist on our curve");
        }
    }
}
