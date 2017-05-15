<?php

namespace Rimelek\IPUtil\Test;

use PHPUnit\Framework\TestCase;
use Rimelek\IPUtil\IPv6Address;
use Rimelek\IPUtil\IPv6Range;

class IPv6RangeTest extends TestCase
{
    public function testFromBinaryInterval()
    {
        $a = IPv6Address::fromString('1:2:3:4:5:6:7:8');
        $b = IPv6Address::fromString('8:7:6:5:4:3:2:1');

        $range = IPv6Range::fromBinaryInterval($a->toBinary(), $b->toBinary());

        $this->assertInstanceOf(IPv6Range::class, $range);
    }

    public function testFromIPInterval()
    {
        $a = IPv6Address::fromString('1:2:3:4:5:6:7:8');
        $b = IPv6Address::fromString('8:7:6:5:4:3:2:1');

        $range = IPv6Range::fromIPInterval($a, $b);

        $this->assertInstanceOf(IPv6Range::class, $range);
    }


    public function dataProviderFromIPWithCIDRPrefix()
    {
        return [
            ["\x7F\x00\x00\x01", 32, "\x7F\x00\x00\x01", "\x7F\x00\x00\x01"],
            ["\x7F\x00\x00\x01", 1,  "\x00\x00\x00\x00", "\x7F\xFF\xFF\xFF"],
            ["\x80\x00\x00\x01", 1,  "\x80\x00\x00\x00", "\xFF\xFF\xFF\xFF"],
            ["\x80\x00\x80\xFF", 17, "\x80\x00\x80\x00", "\x80\x00\xFF\xFF"],
            ["\x80\x00\x80\xFF", 18, "\x80\x00\x80\x00", "\x80\x00\xBF\xFF"],
        ];
    }

    /**
     * @dataProvider dataProviderFromIPWithCIDRPrefix
     * @param $binary
     * @param $prefix
     * @param $minBinary
     * @param $maxBinary
     */
    public function testFromIPWithCIDRPrefix($binary, $prefix, $minBinary, $maxBinary)
    {
        $minSuffix = str_repeat("\x00", 12);
        $maxSuffix = str_repeat("\xFF", 12);
        $ip = IPv6Address::fromBinary($binary . $minSuffix);
        $range = IPv6Range::fromIPWithCIDRPrefix($ip, $prefix);
        $this->assertEquals($minBinary . $minSuffix, $range->getMinIP()->toBinary());
        $this->assertEquals($maxBinary . $maxSuffix, $range->getMaxIP()->toBinary());
    }
}
