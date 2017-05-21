<?php

namespace Rimelek\IPUtil;


interface IPRangeFactoryInterface
{
    /**
     *
     * @param string $min
     * @param string $max
     * @return static
     */
    public static function fromBinaryInterval($min, $max);

    /**
     * Create an IPRange instance from the minimum and maximum IP addresses
     *
     * @param AbstractIPAddress $min
     * @param AbstractIPAddress $max
     * @return static
     */
    public static function fromIPInterval($min, $max);

    /**
     *
     * @param IPAddressInterface $IP
     * @param int $CIDRPrefix
     * @return static
     */
    public static function fromIPWithCIDRPrefix($IP, $CIDRPrefix);
}