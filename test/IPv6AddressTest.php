<?php

namespace Rimelek\IPUtil\Test;

use Rimelek\IPUtil\IPv4Address;
use Rimelek\IPUtil\IPv6Address;
use PHPUnit\Framework\TestCase;
use Rimelek\IPUtil\IPv6Range;


class IPv6AddressTest extends TestCase
{    
    public function testFromString()
    {
        $this->assertEquals(str_repeat("\x00", 16), IPv6Address::fromString('::')->toBinary());
        $this->assertEquals(str_repeat("\x00", 15)."\x01", IPv6Address::fromString('::1')->toBinary());
        $this->assertEquals("\x00\x01".str_repeat("\x00", 14), IPv6Address::fromString('1::')->toBinary());
        $this->assertEquals("\x00\x01".str_repeat("\x00", 13)."\x01", IPv6Address::fromString('1::1')->toBinary());
    }
    
    public function testFromCIDRPrefix()
    {
        $this->assertEquals(str_repeat("\x00", 16), IPv6Address::fromCIDRPrefix(0)->toBinary());
        for ($i = 1; $i < 16; $i++) {
            $this->assertEquals(str_repeat("\xFF", $i).str_repeat("\x00", 16-$i), IPv6Address::fromCIDRPrefix($i*8)->toBinary());
        }
        $this->assertEquals(str_repeat("\xFF", 16), IPv6Address::fromCIDRPrefix(128)->toBinary());
    }

    public function testFromBitString()
    {
        $binaryPrefix = str_repeat("\0", 12);
        $ip = IPv6Address::fromBitString('01111111' . '00000000' . '00000000' . '00000001');
        $this->assertEquals($binaryPrefix . "\x7f\x00\x00\x01", $ip->toBinary());
        $ip = IPv6Address::fromBitString('01111111' . '00000000' . '0000000A' . '00000001');
        $this->assertEquals($binaryPrefix . "\x7f\x00\x00\x01", $ip->toBinary());
    }
    
    public function testIsCompatibleWithIPv4()
    {
        $this->assertTrue(
            IPv6Address::fromBinary(str_repeat("\x00", 10)."\xFF\xFF\x00\x00\x00\x00")
            ->isCompatibleWithIPv4());
        $this->assertTrue(
            IPv6Address::fromBinary(str_repeat("\x00", 10)."\xFF\xFF\x01\x02\x03\x04")
            ->isCompatibleWithIPv4());
        $this->assertTrue(
            IPv6Address::fromBinary(str_repeat("\x00", 10)."\xFF\xFF\xFF\xFF\xFF\xFF")
            ->isCompatibleWithIPv4());
        
        $this->assertFalse(
            IPv6Address::fromBinary(str_repeat("\x00", 10)."\xFF\xFE\x00\x00\x00\x00")
            ->isCompatibleWithIPv4());
        $this->assertFalse(
            IPv6Address::fromBinary(str_repeat("\x00", 10)."\xFF\xFE\x01\x02\x03\x04")
            ->isCompatibleWithIPv4());
        $this->assertFalse(
            IPv6Address::fromBinary(str_repeat("\x00", 10)."\xFF\xFE\xFF\xFF\xFF\xFF")
            ->isCompatibleWithIPv4());
        
        $this->assertFalse(
            IPv6Address::fromBinary(str_repeat("\x00", 9)."\xFF\xFF\xFF\x00\x00\x00\x00")
            ->isCompatibleWithIPv4());
        $this->assertFalse(
            IPv6Address::fromBinary(str_repeat("\x00", 9)."\xFF\xFF\xFF\x01\x02\x03\x04")
            ->isCompatibleWithIPv4());
        $this->assertFalse(
            IPv6Address::fromBinary(str_repeat("\x00", 9)."\xFF\xFF\xFF\xFF\xFF\xFF\xFF")
            ->isCompatibleWithIPv4());
    }
    
    public function testToString()
    {
        $ip1 = IPv6Address::fromBinary(str_repeat("\x00", 10)."\xFF\xFF\x00\x00\x00\x00");
        $ip2 = IPv6Address::fromBinary("\x00\x00\xFF\xFF\x00\x00\x00\x00\xFF".str_repeat("\x00", 7));
        $ip3 = IPv6Address::fromBinary("\x00\x00\xFF\xFF\x00\x00\x00\x00".str_repeat("\xFF", 8));
        $withoutArgs = $ip1->toString();
        $withShort = $ip1->toString(IPv6Address::LENGTH_SHORT);
        $withMedium = $ip1->toString(IPv6Address::LENGTH_MEDIUM);
        $withLong = $ip1->toString(IPv6Address::LENGTH_LONG);
        
        $this->assertEquals($withoutArgs, $withShort);
        $this->assertEquals("::ffff:0:0", $withShort);
        $this->assertEquals(str_repeat("0:", 5)."ffff:0:0", $withMedium);
        $this->assertEquals(str_repeat("0000:", 5)."ffff:0000:0000", $withLong);
        $this->assertEquals(str_repeat("0:", 5)."ffff:0:0", $withMedium);
        
        $this->assertEquals("0:ffff:0:0:ff00::", $ip2->toString(IPv6Address::LENGTH_SHORT));
        $this->assertEquals("0:ffff::ffff:ffff:ffff:ffff", $ip3->toString(IPv6Address::LENGTH_SHORT));

        $this->assertEquals($ip1->toString(), (string) $ip1);
    }

    public function testFromIPv4()
    {
        $binary = "\x7F\x00\x00\x01";
        $ipv4 = IPv4Address::fromBinary($binary);
        $ipv6 = IPv6Address::fromIPv4($ipv4);
        $this->assertEquals("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x01", $ipv6->toBinary());
    }

    public function testIsCompatibleWithIP4()
    {
        $compatible = IPv6Address::fromBinary("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x01");
        $inCompatible = IPv6Address::fromBinary("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x01");

        $this->assertTrue($compatible->isCompatibleWithIPv4(), "IPv4 compatibility check has failed.");
        $this->assertFalse($inCompatible->isCompatibleWithIPv4(), "IPv4 incompatibility check has failed.");
    }

    public function testToIPv4()
    {
        $ip = IPv6Address::fromBinary("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x01");
        $this->assertEquals("\x7f\x00\x00\x01", $ip->toIPv4()->toBinary());
    }

    /**
     * @expectedException \Exception
     */
    public function testToIPv4Exception()
    {
        if (method_exists($this, 'exceptException')) {
            $this->expectException(\Exception::class);
        }
        $ip = IPv6Address::fromBinary("\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x01");
        $ip->toIPv4();
    }

    public function dataProviderToCIDRNotations()
    {
        return [
            ["\x7F\x00\x00\x01", "\x7F\x00\x00\x01", [
                ["\x7F\x00\x00\x01", "\x7F\x00\x00\x01"],
            ]],
            ["\x7F\x00\x00\x01", "\x7F\x00\x00\x02", [
                ["\x7F\x00\x00\x01", "\x7F\x00\x00\x01"],
                ["\x7F\x00\x00\x02", "\x7F\x00\x00\x02"],
            ]],
            ["\x7F\x00\x00\x00", "\x7F\x00\x00\x02", [
                ["\x7F\x00\x00\x00", "\x7F\x00\x00\x01"],
                ["\x7F\x00\x00\x02", "\x7F\x00\x00\x02"],
            ]],
            ["\x7F\x00\x00\x00", "\x7F\x00\x00\x03", [
                ["\x7F\x00\x00\x00", "\x7F\x00\x00\x03"],
            ]],
            ["\x7F\x00\x00\x01", "\x7F\x00\x00\x03", [
                ["\x7F\x00\x00\x01", "\x7F\x00\x00\x01"],
                ["\x7F\x00\x00\x02", "\x7F\x00\x00\x03"],
            ]],
            ["\x7F\x00\x00\x00", "\x80\x00\x00\x01", [
                ["\x7F\x00\x00\x00", "\x7F\xFF\xFF\xFF"],
                ["\x80\x00\x00\x00", "\x80\x00\x00\x01"],
            ]],
            ["\xC0\x01\x50\x00", "\xC0\x20\x00\xFE", [
                ["\xC0\x01\x50\x00", "\xC0\x01\x5F\xFF"],
                ["\xC0\x01\x60\x00", "\xC0\x01\x7F\xFF"],
                ["\xC0\x01\x80\x00", "\xC0\x01\xFF\xFF"],
                ["\xC0\x02\x00\x00", "\xC0\x03\xFF\xFF"],
                ["\xC0\x04\x00\x00", "\xC0\x07\xFF\xFF"],
                ["\xC0\x08\x00\x00", "\xC0\x0F\xFF\xFF"],
                ["\xC0\x10\x00\x00", "\xC0\x1F\xFF\xFF"],
                ["\xC0\x20\x00\x00", "\xC0\x20\x00\x7F"],
                ["\xC0\x20\x00\x80", "\xC0\x20\x00\xBF"],
                ["\xC0\x20\x00\xC0", "\xC0\x20\x00\xDF"],
                ["\xC0\x20\x00\xE0", "\xC0\x20\x00\xEF"],
                ["\xC0\x20\x00\xF0", "\xC0\x20\x00\xF7"],
                ["\xC0\x20\x00\xF8", "\xC0\x20\x00\xFB"],
                ["\xC0\x20\x00\xFC", "\xC0\x20\x00\xFD"],
                ["\xC0\x20\x00\xFE", "\xC0\x20\x00\xFE"],
            ]],
            ["\x00\x00\x00\x00", "\xFF\xFF\xFF\xFF", [
                ["\x00\x00\x00\x00", "\xFF\xFF\xFF\xFF"],
            ]]
        ];
    }

    /**
     *
     * @dataProvider dataProviderToCIDRNotations
     * @param $from
     * @param $to
     * @param array $expected
     */
    public function testToCIDRNotations($from, $to, $expected)
    {
        $ranges = IPv6Range::fromBinaryInterval($from, $to)->toCIDRNotations();

        if (count($expected) === count($ranges)) {
            $binaryPrefix = str_repeat("\0", 12);
            foreach ($ranges as $i => $range) {
                $this->assertEquals($binaryPrefix . $expected[$i][0], $range->getMinIP()->toBinary());
                $this->assertEquals($binaryPrefix . $expected[$i][1], $range->getMaxIP()->toBinary());
            }
        } else {
            // var_dump($expected, $ranges);
            $this->fail('The size of calculated ranges and expected ranges are different: Expected: ' . count($expected) . ', Actual: ' . count($ranges));
        }
    }
}
