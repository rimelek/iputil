<?php
namespace Rimelek\IPUtil;


class IPv4RangeAbstract extends AbstractIPRange
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
     * @return self
     */
    public static function fromIPInterval($min, $max)
    {
        return parent::fromIPInterval($min, $max);
    }
    
    /**
     * 
     * @param IPv4Address $IP
     * @param int $cidrPrefix 
     * @return self
     */
    public static function fromIPWithCIDRPrefix($IP, $cidrPrefix)
    {
        return parent::fromIPWithCIDRPrefix($IP, $cidrPrefix);
    }
}