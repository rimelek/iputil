<?php
namespace Rimelek\IPUtil;

abstract class AbstractIPAddress implements IPAddressInterface
{
    /**
     * The binary representation of the IP address
     *
     * @var string $binary
     */
    private $binary;
    
    /**
     * @param string $binary Binary representation of the IP address. The binary form of 127.0.0.1 is "\x7F\x00\x00\x01"
     * @param int $sizeInBytes
     */
    protected function __construct($binary, $sizeInBytes)
    {
        $binary = str_pad(substr($binary, -$sizeInBytes), $sizeInBytes, "\0", STR_PAD_LEFT);
        $this->binary = $binary;
    }

    /**
     * Get the binary representation of the IP address
     *
     * The binary form of 127.0.0.1 is "\x7F\x00\x00\x01".
     * However, if you want to display it using echo, you need to use {@link http://php.net/manual/en/function.unpack.php unpack()}
     *
     * @return string
     */
    public function toBinary()
    {
        return $this->binary;
    }

    /**
     * Convert the IP Address to its string representation
     *
     * @return string
     */
    abstract public function toString();

    /**
     * Alias of {@link toString()} without arguments
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }


    /**
     * Convert a character (1 byte) to bit string
     * 
     * If $char contains more than one character, only the first will be used.
     * 
     * If $char is empty string or not a string, then it will be interpreted as 0 byte
     * 
     * @param string $char
     * @return string Bit string
     */
    protected static function charToBitString($char)
    {
        if (!is_string($char) or $char === "") {
            $char = "\0";
        }
        return str_pad(decbin(ord($char[0])), 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * 
     * @param string $bitString
     * @return string
     */
    protected static function bitStringToChar($bitString)
    {
        return chr(bindec(str_pad(substr($bitString, 0, 8), 8, '0', STR_PAD_LEFT)));
    }
    
    /**
     * Generate 0-1 series from the IP address
     * 
     * @return string
     */
    public function toBitString()
    {
        $bin = $this->toBinary();
        $length = strlen($bin);
        $digits = "";
        
        for ($i = 0; $i < $length; $i++) {
            $digits .= self::charToBitString($bin[$i]);
        }
        
        return $digits;
    }

    /**
     * Convert bit string to a binary string
     *
     * @param string $bitString
     * @return string
     */
    protected static function bitStringToBinary($bitString)
    {

        $bitString = preg_replace('~[^1]~', '0', $bitString);
        $length = strlen($bitString);
        $remainder = $length % 8;
        $sizeInBits = $remainder ? $length + (8 - $remainder) : $length;
        $bitString = str_pad($bitString, $sizeInBits, '0', STR_PAD_LEFT);

        $binary = "";
        foreach (str_split($bitString, 8) as $byteBits) {
            $binary .= self::bitStringToChar($byteBits);
        }
        return $binary;
    }
    
    /**
     * Create the inverse version of the IP address
     * 
     * Zeros are converted to ones and ones are converted to zeros
     * It can be used to create a wildcard mask.
     * 
     * @return static
     */
    public function toInverseIP()
    {
        $inverseBinary = ~$this->toBinary();
        return new static($inverseBinary, strlen($inverseBinary));
    }   
    
    /**
     * Check if to IP are equal.
     *
     * $this is equal to $ip if their binary representations are the same.
     *
     * @param IPAddressInterface $ip
     * @return bool 
     */
    public function equals(IPAddressInterface $ip)
    {
        return (
                (get_class($this) === get_class($ip) and $this->toBinary() === $ip->toBinary())
                or
                ($this instanceof IPv4Address ? $this->toIPv6()->toBinary() : $this->toBinary())
                 ===
                ($ip instanceof IPv4Address ? $ip->toIPv6()->toBinary() : $ip->toBinary())
        );
    }
    
    /**
     * C.I.D.R. prefix converted to binary subnet mask
     *
     * @param int $CIDRPrefix
     * @param int $sizeInBits
     * @return string
     */
    protected static function CIDRPrefixToBinaryMask($CIDRPrefix, $sizeInBits)
    {
        $CIDRPrefix = min($CIDRPrefix, $sizeInBits);
        $_1 = intval($CIDRPrefix / 8);
        $_2 = $CIDRPrefix % 8;
        $_3 = ($sizeInBits/8) - ($_1 + intval($_2 !== 0));
        $binary = '';
        if ($_1) {
            $binary = str_repeat("\xFF", $_1);
        }
        if ($_2) {
            $binary .= chr(bindec(str_pad(str_repeat('1', $_2), 8, '0', STR_PAD_RIGHT)));
        }
        if ($_3) {
            $binary .= str_repeat("\0", $_3);
        }
        return $binary;
    }

}
