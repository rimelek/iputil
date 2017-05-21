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
     * Check if two IP are equal.
     *
     * $this is equal to $ip if their binary representations are the same.
     *
     * @param IPAddressInterface $ip
     * @return bool
     */
    public function equals(IPAddressInterface $ip);

}