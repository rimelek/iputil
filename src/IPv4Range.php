<?php
namespace Rimelek\IPUtil;


class IPv4Range extends AbstractIPRange
{
    
    /**
     *
     * @param string $min
     * @param string $max
     * @return self
     */
    public static function fromBinaryInterval($min, $max)
    {
        return self::fromIPInterval(IPv4Address::fromBinary($min), IPv4Address::fromBinary($max));
    }
    
    /**
     * 
     * @param IPv4Address $min
     * @param IPv4Address $max
     * @return IPv4Range|AbstractIPRange
     */
    public static function fromIPInterval($min, $max)
    {
        return parent::fromIPInterval($min, $max);
    }
    
    /**
     * 
     * @param IPv4Address $IP
     * @param int $CIDRPrefix
     * @return IPv4Range|AbstractIPRange
     */
    public static function fromIPWithCIDRPrefix($IP, $CIDRPrefix)
    {
        return parent::fromIPWithCIDRPrefix($IP, $CIDRPrefix);
    }
}