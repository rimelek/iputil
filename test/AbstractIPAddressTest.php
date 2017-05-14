<?php

namespace Rimelek\IPUtil\Test;

use Rimelek\IPUtil\AbstractIPAddress;
use PHPUnit\Framework\TestCase;
use Rimelek\IPUtil\IPv4Address;
use Rimelek\IPUtil\IPv6Address;

class AbstractIPAddressTest extends TestCase
{
    /**
     * @param $class
     * @param $method
     * @return \ReflectionMethod
     */
    private function getPrivateMethod($class, $method)
    {
        $reflection = new \ReflectionClass($class);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }

    public function testCharToDigits()
    {
        $method = $this->getPrivateMethod(AbstractIPAddress::class, 'charToBitString');
        $this->assertEquals('00000000', $method->invoke(null, []));
    }

    public function testToDigits()
    {
        $ip = IPv4Address::fromBinary("\x7f\x00\x00\x01");
        $digits = '01111111' . '00000000' . '00000000' . '00000001';
        $this->assertEquals($digits, $ip->toBitString());
    }

    public function testToInverseIP()
    {
        $ip = IPv4Address::fromBinary("\x7f\x00\x00\x01");
        $inverse = "\x80\xff\xff\xfe";
        $this->assertEquals($inverse, $ip->toInverseIP()->toBinary());
    }

    public function testEquals()
    {
        $ipv61 = IPv6Address::fromBinary("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x01");
        $ipv62 = IPv6Address::fromBinary("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x7f\x00\x00\x01");

        $ipv4 = IPv4Address::fromBinary("\x7f\x00\x00\x01");

        $this->assertTrue($ipv4->equals($ipv61));
        $this->assertFalse($ipv4->equals($ipv62));

        $this->assertTrue($ipv61->equals($ipv4));
        $this->assertFalse($ipv62->equals($ipv4));
    }

    public function testCIDRPrefixToBinaryMask()
    {
        $bytes = [
            "\x00",
            "\x80",
            "\xc0",
            "\xe0",
            "\xf0",
            "\xf8",
            "\xfc",
            "\xfe",
            "\xff"
        ];

        $expectedIPv4 = str_repeat("\x00", 4);
        $expectedIPv6 = str_repeat("\x00", 16);

        for ($i = 0; $i <= 15; $i++) {
            for ($j = 1; $j <= 8; $j++) {
                $expectedIPv4[$i] = $bytes[$j];
                $expectedIPv6[$i] = $bytes[$j];
                $prefix = $i * 8 + $j;
                if ($i <= 3) { // IPv4
                    $binary = IPv4Address::CIDRPrefixToBinaryMask($prefix);
                    $this->assertEquals($expectedIPv4, $binary);
                }
                $binary = IPv6Address::CIDRPrefixToBinaryMask($prefix);
                $this->assertEquals($expectedIPv6, $binary);
            }
        }
    }
}
