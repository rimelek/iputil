<?php
namespace Rimelek\IPUtil;

class IPv6Range extends AbstractIPRange
{
    /**
     * 
     * @param string $min
     * @param string $max
     * @return self
     */
    public static function fromBinaryInterval($min, $max)
    {
        return new self(IPv6Address::fromBinary($min), IPv6Address::fromBinary($max));
    }

    /**
     * 
     * @param IPv6Address $min
     * @param IPv6Address $max
     * @return self
     */
    public static function fromIPInterval($min, $max)
    {
        return parent::fromIPInterval($min, $max);
    }
    
    /**
     *
     * @param IPv6Address $IP Must be IPv6
     * @param int $cidrPrefix
     * @return self
     */
    public static function fromIPWithCIDRPrefix($IP, $cidrPrefix)
    {
        return parent::fromIPWithCIDRPrefix($IP, $cidrPrefix);
    }
}

