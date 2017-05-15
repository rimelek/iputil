<?php
namespace Rimelek\IPUtil;
use Exception;

/**
 * IPv4
 */
class IPv4Address extends AbstractIPAddress implements IPAddressInterface
{
    /**
     * First octet high order bits of IP classes
     *
     * @var array
     */
    private static $highOrderBitsOfIPv4Class = array(
        'A' => '0',
        'B' => '10',
        'C' => '110',
        'D' => '1110',
        'E' => '1111'
    );
    
    /**
     * Create IPv4Address instance from IPv4 string
     * 
     * @param string $IPv4 Ex.: 10.8.1.5
     * @return IPv4Address
     */
    public static function fromString($IPv4)
    {
        $parts = explode('.', $IPv4, 4) + [0,0,0,0];
        
        $binary = '';
        foreach ($parts as &$part) {
            $binary .= chr(intval($part));
        }
        return self::fromBinary($binary);
    }
    
    /**
     * Create IPv4Address instance from CIDR prefix
     * 
     * @param int $cidrPrefix CIDRPrefix
     * @return IPv4Address|AbstractIPAddress
     */
    public static function fromCIDRPrefix($cidrPrefix)
    {
        return parent::fromCIDRPrefix($cidrPrefix);
    }
    
    /**
     * Create IPv4Address instance from IPv6 instance
     * 
     * @param IPv6Address $IPv6
     * @return IPv4Address
     */
    public static function fromIPv6(IPv6Address $IPv6)
    {
        return $IPv6->toIPv4();
    }
    
    /**
     * Get the IP class of the IP address
     *
     * @return string
     */
    public function getIPClass()
    {
        $class = "";
        $firstByte = str_pad(decbin(ord($this->toBinary()[0])), 8, '0', STR_PAD_LEFT);
        foreach (self::$highOrderBitsOfIPv4Class as $currentClass => $bits) {
            if (substr($firstByte, 0, strlen($bits)) === $bits) {
                $class = $currentClass;
                break;
            }
        }
        return $class;
    }

    /**
     * Get the High Order Bits of the given IP class
     *
     * @param string $class
     * @return string
     * @throws Exception
     */
    public static function getHighOrderBitsOfIPv4Class($class)
    {
        $class = strtoupper($class);

        if (!isset(self::$highOrderBitsOfIPv4Class[$class])) {
            throw new Exception('"' . $class . '" is not a valid IP class! It must be A, B, C, D or E');
        }
        return self::$highOrderBitsOfIPv4Class[$class];
    }
    
    /**
     * Convert to IPv6 address string
     *
     * @return string
     */
    public function toString()
    {
        $binary = $this->toBinary();
        return ord($binary[0])
            . '.' . ord($binary[1])
            . '.' . ord($binary[2])
            . '.' . ord($binary[3]);
    }
    
    /**
     * Convert to IPv6
     * 
     * @return IPv6Address
     */
    public function toIPv6()
    {
        return IPv6Address::fromBinary("\0\0\0\0\0\0\0\0\0\0\xFF\xFF".$this->toBinary()) ;
    }

    /**
     * Create an IPv4 address from binary data
     *
     * @param string $binary
     * @return AbstractIPAddress|IPv4Address
     */
    public static function fromBinary($binary)
    {
        return parent::fromBinary($binary);
    }

    /**
     * @inheritDoc
     */
    public static function fromBitString($bitString)
    {
        return parent::fromBitString($bitString);
    }

    /**
     * @inheritDoc
     */
    public static function CIDRPrefixToBinaryMask($cidrPrefix)
    {
        return parent::CIDRPrefixToBinaryMask($cidrPrefix);
    }
}

