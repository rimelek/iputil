<?php

namespace Rimelek\IPUtil;


interface IPAddressFactoryInterface
{
    /**
     * Create IP address instance from string
     *
     * @param string $IP Ex.: 10.8.1.5
     * @return IPAddressInterface
     */
    public static function fromString($IP);

    /**
     * Create an IP address from 0-1 series
     *
     * @param string $bitString
     * @return IPAddressInterface
     */
    public static function fromBitString($bitString);

    /**
     * Create an IP address from binary data
     *
     * @param string $binary
     * @return IPAddressInterface
     */
    public static function fromBinary($binary);

    /**
     * Create IP Address instance from C.I.D.R. prefix
     *
     * @param int $CIDRPrefix CIDRPrefix
     * @return IPAddressInterface
     */
    public static function fromCIDRPrefix($CIDRPrefix);
}