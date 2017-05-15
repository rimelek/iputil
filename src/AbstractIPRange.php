<?php
namespace Rimelek\IPUtil;

abstract class AbstractIPRange
{
    /**
     *
     * @var IPAddressInterface $min
     */
    private $min;
    
    /**
     *
     * @var IPAddressInterface $max
     */
    private $max;
    
    /**
     * If it was created by fromIPv4WithCIDRPrefix
     *
     * @var int $cidrPrefix
     */
    private $cidrPrefix = null;

    /**
     * @param IPAddressInterface $min
     * @param IPAddressInterface $max
     */
    protected function __construct(IPAddressInterface $min, IPAddressInterface $max)
    {
        $min = clone $min;
        $max = clone $max;

        $bMin = $min->toBinary();
        $bMax = $max->toBinary();
        $cmp = strcmp($bMin, $bMax) < 0;
        $this->min = $cmp ? $min : $max;
        $this->max = $cmp ? $max : $min;
    }
    
    /**
     * 
     * @return IPAddressInterface
     */
    public function getMinIP()
    {
        return $this->min;
    }
    
    /**
     * 
     * @return IPAddressInterface
     */
    public function getMaxIP()
    {
        return $this->max;
    }
    
    /**
     * CIDR Prefix
     * 
     * @return int|null
     */
    public function getCIDRPrefix()
    {
        return $this->cidrPrefix;
    }
    
    /**
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * min - max
     * 
     * @return string
     */
    public function toString()
    {
        return $this->getMinIP() . ' - ' . $this->getMaxIP();
    }
    
    /**
     * The number of bits same in both IP address from left to right
     *
     * @param string $min 0-1 series
     * @param string $max 0-1 series
     * @return int
     */
    protected static function countFixBits($min, $max)
    {
        $i = 0; 
        $length = min(strlen($min), strlen($max));
        while($i < $length and $min[$i] === $max[$i]) {
            $i++;
        }
        return $i;
    }

    /**
     * Create an IPRange instance by the minimum and maximum IP addresses
     *
     * @param AbstractIPAddress $min
     * @param AbstractIPAddress $max
     * @return AbstractIPRange
     */
    protected static function fromIPInterval($min, $max)
    {
        return new static($min, $max);
    }

    /**
     *
     * @param AbstractIPAddress $IP
     * @param int $cidrPrefix
     * @return AbstractIPRange
     */
    protected static function fromIPWithCIDRPrefix($IP, $cidrPrefix)
    {
        $class = self::getSupportedIPClassName();

        $cidrBinary = $IP->fromCIDRPrefix($cidrPrefix)->toBinary();
        $min = $IP->toBinary() & $cidrBinary;
        $max = $min | (~$cidrBinary);
        $range = self::fromIPInterval(
            call_user_func([get_class($IP), 'fromBinary'], $min),
            call_user_func([get_class($IP), 'fromBinary'], $max),
            $class);
        $range->cidrPrefix = $cidrPrefix;
        return $range;
    }
    
    /**
     * Check if $this is a part of $range
     * 
     * @param self $range
     * @return bool
     */
    public function in(self $range)
    {
        $thisMin = $this->getMinIP();
        $thisMax = $this->getMaxIP();
        
        $rangeMin = $range->getMinIP();
        $rangeMax = $range->getMaxIP();
        
        if ($range instanceof IPv6Range and $this instanceof IPv4Range) {
            $thisMin = $thisMin->toIPv6();
            $thisMax = $thisMax->toIPv6();
        }
        
        if ($range instanceof IPv4Range and $this instanceof IPv6Range) {
            $rangeMin = $rangeMin->toIPv6();
            $rangeMax = $rangeMax->toIPv6();
        }
        
        return ($thisMin->toBinary() >= $rangeMin->toBinary() and $thisMax->toBinary() <= $rangeMax->toBinary()); 
    }

    /**
     * @return static[]
     */
    public function toCIDRPrefixedRanges()
    {
        $sizeInBits = static::class === IPv6Range::class ? 128 : 32;
        $sizeInBytes = static::class === IPv6Range::class ? 16 : 4;
        // In case of full range
        if ($this->getMinIP()->toBinary() === str_repeat("\0", $sizeInBytes)
            and $this->getMaxIP()->toBinary() === str_repeat("\xFF", $sizeInBytes)) {
            return [self::fromIPWithCIDRPrefix($this->getMinIP(), 0)];
        }
        // If minimum and maximum are the same, then there is only one IP address
        if ($this->getMaxIP()->equals($this->getMinIP())) {
            return [self::fromIPWithCIDRPrefix($this->getMinIP(), $sizeInBits)];
        }
        // The minimum IP address can only be even and the maximum must be odd.
        $firstAndLastRange = $this->cleanBoundariesOfRange();

        $ranges = [];
               
        if ($firstAndLastRange['firstRange'] !== null) {
            $ranges[] = $firstAndLastRange['firstRange'];
        }

        if ($this->getMinIP()->equals($this->getMaxIP())) {
            $ranges[] = self::fromIPWithCIDRPrefix($this->getMinIP(), $sizeInBits);
        } else {
            $dmin = $this->getMinIP()->toBitString();
            $dmax = $this->getMaxIP()->toBitString();
            $countFixBits = self::countFixBits($dmin, $dmax);

            if (rtrim(substr($dmin, $countFixBits), '0') === "" and 
                rtrim(substr($dmax, $countFixBits), '1') === "") {
                $ranges[] = self::fromIPWithCIDRPrefix($this->getMinIP()->fromBitString($dmin), $countFixBits);
            } else {
                // 1.
                $pos = strrpos($dmin, '1', $countFixBits);
                if ($pos !== false) {
                    $cidrPrefix = $pos+1;
                    $ranges[] = self::fromIPWithCIDRPrefix($this->getMinIP()->fromBitString($dmin), $cidrPrefix);
                    $dmin = rtrim(rtrim($dmin, '0'), '1');
                    // 2.
                    $pos = strlen($dmin)-1;
                    while ($pos > $countFixBits) {
                        $dmin{$pos} = '1';
                        $ranges[] = self::fromIPWithCIDRPrefix($this->getMinIP()->fromBitString(str_pad($dmin, $sizeInBits, '0', STR_PAD_RIGHT)), $pos+1);
                        $dmin = rtrim($dmin, '1');
                        $pos = strlen($dmin)-1;
                    }
                }
                // 4.
                $end = max((strrpos($dmax, '0') ?: -1)+1, $countFixBits);
                $pos = $countFixBits;
                while (($pos = strpos($dmax, '1', $pos+1)) !== false and $pos < $end) {
                    $ranges[] = self::fromIPWithCIDRPrefix($this->getMinIP()->fromBitString(substr_replace($dmax, '0', $pos, 1)), $pos+1);
                }
                
                // 5.
                $ranges[] = self::fromIPWithCIDRPrefix($this->getMinIP()->fromBitString($dmax), $end);
            }                      
        }
        if ($firstAndLastRange['lastRange'] !== null) {
            $ranges[] = $firstAndLastRange['lastRange'];
        }
        
        return $ranges;
    }
    
    /**
     * Fix boundaries of a range by parity
     * 
     * The minimum IP address can only be even and the maximum must be odd.
     *
     * @return self[]
     * 
     */
    private function cleanBoundariesOfRange()
    {
        $ranges = [
            'firstRange' => null,
            'lastRange' => null
        ];

        $minIsOdd = (substr($this->min->toBinary(), -1) & "\x1") === "\x1";
        $maxIsOdd = (substr($this->max->toBinary(), -1) & "\x1") === "\x1";

        if ($minIsOdd) {
            $ranges['firstRange'] = self::fromIPWithCIDRPrefix(clone $this->min, strlen($this->min->toBinary()) * 8);
            $this->incrementMinIP();
        }

        if (!$this->min->equals($this->max) and !$maxIsOdd) {
            $ranges['lastRange'] = self::fromIPWithCIDRPrefix(clone $this->max, strlen($this->max->toBinary()) * 8);
            $this->decrementMaxIP();
        }
        
        return $ranges;
        
    }

    /**
     * @return string
     */
    private function getSupportedIPClassName()
    {
        return static::class === IPv6Range::class ? IPv6Address::class : IPv4Address::class;
    }

    /**
     * Subtract 1 from the maximum IP address if it is possible
     *
     */
    private function decrementMaxIP()
    {
        $binary = $this->max->toBinary();
        $length = strlen($binary);

        for ($i = $length-1; $i >= 0; $i--) {
            $char = $binary[$i];
            if ($char === "\0") {
                $binary[$i] = chr(255);
            } else {
                $binary[$i] = chr(ord($char)-1);
                break;
            }
        }
        $this->max = $length === 16
            ? IPv6Address::fromBinary($binary)
            : IPv4Address::fromBinary($binary);
    }

    /**
     * Add 1 to the minimum IP address if it is possible
     *
     */
    private function incrementMinIP()
    {
        $binary = $this->min->toBinary();
        $length = strlen($binary);

        for ($i = $length-1; $i >= 0; $i--) {
            $char = $binary[$i];
            if ($char === "\xFF") {
                $binary[$i] = chr(0);
            } else {
                $binary[$i] = chr(ord($char)+1);
                break;
            }
        }

        $this->min = $length === 16
            ? IPv6Address::fromBinary($binary)
            : IPv4Address::fromBinary($binary);
    }
}