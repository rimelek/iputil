<?php
namespace Rimelek\IPUtil;

abstract class AbstractIPRange implements IPRangeInterface
{
    /**
     *
     * @var AbstractIPAddress $min
     */
    private $min;
    
    /**
     *
     * @var AbstractIPAddress $max
     */
    private $max;
    
    /**
     * If it was created by fromIPv4WithCIDRPrefix
     *
     * @var int $CIDRPrefix
     */
    private $CIDRPrefix = null;

    /**
     * @param AbstractIPAddress $min
     * @param AbstractIPAddress $max
     */
    protected function __construct(AbstractIPAddress $min, AbstractIPAddress $max)
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
     * @return AbstractIPAddress
     */
    public function getMinIP()
    {
        return $this->min;
    }
    
    /**
     * 
     * @return AbstractIPAddress
     */
    public function getMaxIP()
    {
        return $this->max;
    }
    
    /**
     * C.I.D.R. Prefix
     * 
     * @return int|null
     */
    public function getCIDRPrefix()
    {
        return $this->CIDRPrefix;
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
        return $this->getMinIP()->toString()
            . ' - '
            . $this->getMaxIP()->toString();
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
     *
     * @param IPAddressInterface $IP
     * @param int $CIDRPrefix
     * @return static
     */
    public static function fromIPWithCIDRPrefix($IP, $CIDRPrefix)
    {
        /* @var $class IPv4Address|IPv6Address */
        $class = get_class($IP);
        $CIDRBinary = $class::fromCIDRPrefix($CIDRPrefix)->toBinary();
        $min = $IP->toBinary() & $CIDRBinary;
        $max = $min | (~$CIDRBinary);

        $range = new static($class::fromBinary($min), $class::fromBinary($max));
        $range->CIDRPrefix = $CIDRPrefix;
        return $range;
    }
    
    /**
     * Check if $this is a part of $range
     * 
     * @param IPRangeInterface $range
     * @return bool
     */
    public function in(IPRangeInterface $range)
    {
        $thisMin = $this->getMinIP();
        $thisMax = $this->getMaxIP();
        
        $rangeMin = $range->getMinIP();
        $rangeMax = $range->getMaxIP();
        
        if ($range instanceof IPv6Range and $this instanceof IPv4Range) {
            /* @var $thisMin IPv4Address */
            $thisMin = $thisMin->toIPv6();
            /* @var $thisMax IPv4Address */
            $thisMax = $thisMax->toIPv6();
        }
        
        if ($range instanceof IPv4Range and $this instanceof IPv6Range) {
            /* @var $rangeMin IPv4Address */
            $rangeMin = $rangeMin->toIPv6();
            /* @var $rangeMax IPv4Address */
            $rangeMax = $rangeMax->toIPv6();
        }
        
        return ($thisMin->toBinary() >= $rangeMin->toBinary() and $thisMax->toBinary() <= $rangeMax->toBinary()); 
    }

    /**
     * @return static[]
     */
    public function toCIDRNotations()
    {
        /* @var $class IPAddressFactoryInterface */
        $class = get_class($this->getMinIP());
        $minIPBinary = $this->getMinIP()->toBinary();
        $maxIPBinary = $this->getMaxIP()->toBinary();
        $minIPLength = strlen($minIPBinary);
        $sizeInBits = $minIPLength * 8;

        // In case of full range
        if (trim($minIPBinary, "\0") === "" and trim($maxIPBinary, "\xFF") === "") {
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
            $minBS = $this->getMinIP()->toBitString();
            $maxBS = $this->getMaxIP()->toBitString();
            $countFixBits = self::countFixBits($minBS, $maxBS);

            if (rtrim(substr($minBS, $countFixBits), '0') === "" and
                rtrim(substr($maxBS, $countFixBits), '1') === "") {
                $ranges[] = self::fromIPWithCIDRPrefix($class::fromBitString($minBS), $countFixBits);
            } else {
                // 1.
                $pos = strrpos($minBS, '1', $countFixBits);
                if ($pos !== false) {
                    $CIDRPrefix = $pos+1;
                    $ranges[] = self::fromIPWithCIDRPrefix($class::fromBitString($minBS), $CIDRPrefix);
                    $minBS = rtrim(rtrim($minBS, '0'), '1');
                    // 2.
                    $pos = strlen($minBS)-1;
                    while ($pos > $countFixBits) {
                        $minBS[$pos] = '1';
                        $ranges[] = self::fromIPWithCIDRPrefix($class::fromBitString(str_pad($minBS, $sizeInBits, '0', STR_PAD_RIGHT)), $pos+1);
                        $minBS = rtrim($minBS, '1');
                        $pos = strlen($minBS)-1;
                    }
                }
                // 4.
                $end = max((strrpos($maxBS, '0') ?: -1)+1, $countFixBits);
                $pos = $countFixBits;
                while (($pos = strpos($maxBS, '1', $pos+1)) !== false and $pos < $end) {
                    $ranges[] = self::fromIPWithCIDRPrefix($class::fromBitString(substr_replace($maxBS, '0', $pos, 1)), $pos+1);
                }
                
                // 5.
                $ranges[] = self::fromIPWithCIDRPrefix($class::fromBitString($maxBS), $end);
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
     * @return self[] Associative array. Keys: firstRange, lastRange
     * 
     */
    private function cleanBoundariesOfRange()
    {
        /* @var $ranges self[]  */
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