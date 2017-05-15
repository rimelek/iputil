<?php

namespace Rimelek\IPUtil;


interface IPAddressInterface
{
    /**
     * Get the binary representation of the IP address
     *
     * @return string
     */
    public function toBinary();

    /**
     * Convert the IP Address to its string representation
     *
     * @return string
     */
    public function toString();

    /**
     * Generate 0-1 series from the IP address
     *
     * @return string
     */
    public function toBitString();

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
     * Create IPv4Address instance from C.I.D.R. prefix
     *
     * @param int $CIDRPrefix CIDRPrefix
     * @return self
     */
    public static function fromCIDRPrefix($CIDRPrefix);

    /**
     * Check if to IP are equal.
     *
     * $this is equal to $ip if their binary representations are the same.
     *
     * @param IPAddressInterface $ip
     * @return bool
     */
    public function equals(IPAddressInterface $ip);

    /**
     * C.I.D.R. prefix converted to binary subnet mask
     *
     * @param int $CIDRPrefix
     * @return string
     */
    public static function CIDRPrefixToBinaryMask($CIDRPrefix);
}