<?php
namespace Rimelek\IPUtil;
use Exception;

/**
 * IPv6
 */
class IPv6Address extends AbstractIPAddress implements IPAddressFactoryInterface
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
     * @param string $IP
     * @return IPv6Address
     */
    public static function fromString($IP)
    {
        $pos = strpos($IP, '::');
        if ($pos !== false) {
            $left = substr($IP, 0, $pos);
            $right = substr($IP, $pos + 2);
            $cLeft = substr_count($left, ':');
            $cRight = substr_count($right, ':');
            if ($IP === '::') {
                $IP = '0:0:0:0:0:0:0:0';
            } elseif ($pos === 0) {
                $IP = str_repeat('0:', 8 - ($cRight + 1)).$right;
            } elseif ($pos === strlen($IP) - 2) {
                $IP = $left.str_repeat(':0', 8 - ($cLeft + 1));
            } else {
                $IP = $left.str_repeat(':0', 8 - ($cLeft + $cRight + 2)).':'.$right;
            }

        }
        
        $IPv6Parts = explode(':', $IP);
        $bytes = "";
        for ($i = count($IPv6Parts)-1; $i >= 0; $i--) {
            $dec = base_convert($IPv6Parts[$i] ?: '0', 16, 10);
            $bytes = chr(($dec >> 8) & 255).chr($dec & 255).$bytes;
        }
        return self::fromBinary($bytes);
    }
    
    /**
     * Create IPv6Address instance from C.I.D.R. prefix
     * 
     * @param int $CIDRPrefix Integer from 0 to 128
     * @return IPv6Address|AbstractIPAddress
     */
    public static function fromCIDRPrefix($CIDRPrefix)
    {
        return self::fromBinary(self::CIDRPrefixToBinaryMask($CIDRPrefix, 128));
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
            $len >= 6  // minimum 6 byte
            and substr($binary, 0, -6) === str_repeat("\0", $len-6) // zeros before the last 6 byte
            and substr($binary, -6, -4) === "\xFF\xFF" // 5. and 6. bytes are 255
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
     * Create an IP address from binary data
     *
     * @param string $binary
     * @return AbstractIPAddress|IPv6Address
     */
    public static function fromBinary($binary)
    {
        return new static($binary, 16);
    }

    /**
     * {@inheritDoc}
     */
    public static function fromBitString($bitString)
    {
        return self::fromBinary(parent::bitStringToBinary($bitString));
    }
}
