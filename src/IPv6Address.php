<?php
namespace Rimelek\IPUtil;
use Exception;

/**
 * IPv6
 */
class IPv6Address extends AbstractIPAddress implements IPAddressInterface
{
    /**
     * Medium length. When a block contains only zeros, only one zero is displayed
     */
    const LENGTH_MEDIUM = 'medium';
    /**
     * Short form. If zeros are optional, they are not displayed at all.
     */
    const LENGTH_SHORT = 'short';
    /**
     * Long form. All zeros are displayed without shortening
     */
    const LENGTH_LONG = 'long';
        
    /**
     * Create IPv6Address instance from IPv6 string
     * 
     * @param string $IPv6
     * @return IPv6Address
     */
    public static function fromString($IPv6)
    {
        $pos = strpos($IPv6, '::');
        if ($pos !== false) {
            $left = substr($IPv6, 0, $pos);
            $right = substr($IPv6, $pos + 2);
            $cLeft = substr_count($left, ':');
            $cRight = substr_count($right, ':');
            if ($IPv6 === '::') {
                $IPv6 = '0:0:0:0:0:0:0:0';
            } elseif ($pos === 0) {
                $IPv6 = str_repeat('0:', 8 - ($cRight + 1)).$right;
            } elseif ($pos === strlen($IPv6) - 2) {
                $IPv6 = $left.str_repeat(':0', 8 - ($cLeft + 1));
            } else {
                $IPv6 = $left.str_repeat(':0', 8 - ($cLeft + $cRight + 2)).':'.$right;
            }

        }
        
        $IPv6Parts = explode(':', $IPv6);
        $bytes = "";
        for ($i = count($IPv6Parts)-1; $i >= 0; $i--) {
            $dec = base_convert($IPv6Parts[$i] ?: '0', 16, 10);
            $bytes = chr(($dec >> 8) & 255).chr($dec & 255).$bytes;
        }
        return self::fromBinary($bytes);
    }
    
    /**
     * Create IPv6Address instance from CIDR prefix
     * 
     * @param int $cidrPrefix Integer from 0 to 128
     * @return IPv6Address
     */
    public static function fromCIDRPrefix($cidrPrefix)
    {
        return self::fromBinary(self::CIDRPrefixToBinaryMask($cidrPrefix));
    }
    
    /**
     * Create IPv6Address instance from IPv4 instance
     * 
     * @param IPv4Address $IPv4
     * @return IPv6Address
     */
    public static function fromIPv4(IPv4Address $IPv4)
    {
        return $IPv4->toIPv6();
    }

    /**
     *
     * @param string $length
     *  - IPv6::LENGTH_LONG: Full length address containing all zeros.
     *  - IPv6::LENGTH_MEDIUM: Leading zeros are removed from each part of the address.
     *  - IPv6::LENGTH_SHORT: Longest series of zeros is also removed from the address.
     * @return string IPv6 address in full or compressed format
     */
    public function toString($length = self::LENGTH_SHORT)
    {
        $unpacked = unpack('H*', $this->toBinary());
        $parts = str_split($unpacked[1], 4);
        if ($length === self::LENGTH_MEDIUM or $length === self::LENGTH_SHORT) {
            foreach ($parts as &$part) {
                $part = ltrim($part, '0') ?: '0';
            }
        }
        $ip =  implode(':', $parts); 
        if ($length === self::LENGTH_SHORT) {
            $matches = array();
            if(preg_match_all('~(^|:)(0(:0)+)(:|$)~', $ip, $matches)) {
                $len = floor(max(array_map('strlen', $matches[2]))/2);
                $ip = str_replace(':::', '::', preg_replace('~(^|:)'.str_repeat('0:', $len).'0(:|$)~', '$1:$2', $ip, 1));
            }
        }
        return $ip;
    }
    
    /**
     * Check if the IP address is compatible with the IPv4.
     * 
     * @return boolean
     */
    public function isCompatibleWithIPv4()
    {
        $binary = $this->toBinary();
        $len = strlen($binary);
        return (
            $len >= 6  //minimum 6 bájt
            and substr($binary, 0, -6) === str_repeat("\0", $len-6) //Az utolsó 6 bájt előtt nulla
            and substr($binary, -6, -4) === "\xFF\xFF" //Az 5. és 6. bájt 255
        );
    }
    
    /**
     * Convert IPv6 address to IPv4 address if it is possible
     * 
     * @return IPv4Address
     * @throws Exception If it cannot be converted to IPv4 address
     */
    public function toIPv4()
    {
        if ($this->isCompatibleWithIPv4()) {
            return IPv4Address::fromBinary($this->toBinary());
        }
        throw new Exception("IPv6 address is incompatible with IPv4: ".$this->toString());
    }

    /**
     * {@inheritDoc}
     */
    public static function fromBinary($binary)
    {
        return parent::fromBinary($binary);
    }

    /**
     * {@inheritDoc}
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
