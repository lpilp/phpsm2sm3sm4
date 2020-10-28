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
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;

/**
 * This is the contract for implementing EcDH (EC Diffie Hellman).
 */
interface EcDHInterface
{

    /**
     * Calculates and returns the shared key for the exchange.
     *
     * @return \GMP
     */
    public function calculateSharedKey(): \GMP;

    /**
     * @return PublicKeyInterface
     */
    public function createMultiPartyKey(): PublicKeyInterface;

    /**
     * Sets the sender's key.
     *
     * @param PrivateKeyInterface $key
     */
    public function setSenderKey(PrivateKeyInterface $key);

    /**
     * Sets the recipient key.
     *
     * @param  PublicKeyInterface $key
     * @return void
     */
    public function setRecipientKey(PublicKeyInterface $key);
}
