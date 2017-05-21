<?php

namespace Rimelek\IPUtil;


interface IPRangeFactoryInterface
{
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