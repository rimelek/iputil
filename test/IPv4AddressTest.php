<?php

namespace Rimelek\IPUtil\Test;

use Rimelek\IPUtil\IPv4Address;
use PHPUnit\Framework\TestCase;
use Rimelek\IPUtil\IPv6Address;


class IPv4AddressTest extends TestCase
{
    public function testFromString()
    {
        $this->assertEquals("\x7F\x00\x00\x01", IPv4Address::fromString('127.0.0.1')->toBinary());
    }

    public function testFromBinary()
    {
        $binary = "\x7F\x00\x00\x01"; // 127.0.0.1
        $this->assertEquals($binary, IPv4Address::fromBinary($binary)->toBinary());
    }

    public function testFromCIDRPrefix()
    {
        $this->assertEquals("\x00\x00\x00\x00", IPv4Address::fromCIDRPrefix(0)->toBinary());
        $this->assertEquals("\xFF\x00\x00\x00", IPv4Address::fromCIDRPrefix(8)->toBinary());
        $this->assertEquals("\xFF\xFF\x00\x00", IPv4Address::fromCIDRPrefix(16)->toBinary());
        $this->assertEquals("\xFF\xFF\xFF\x00", IPv4Address::fromCIDRPrefix(24)->toBinary());
        $this->assertEquals("\xFF\xFF\xFF\xFF", IPv4Address::fromCIDRPrefix(32)->toBinary());
    }

    public function testFromBitString()
    {
        $ip = IPv4Address::fromBitString('01111111' . '00000000' . '00000000' . '00000001');
        $this->assertEquals("\x7f\x00\x00\x01", $ip->toBinary());
        $ip = IPv4Address::fromBitString('01111111' . '00000000' . '0000000A' . '00000001');
        $this->assertEquals("\x7f\x00\x00\x01", $ip->toBinary());
    }

    public function testFromIPv6()
    {
        $binary = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x7f\x00\x00\x01";
        $ipv6 = IPv6Address::fromBinary($binary);
        $ipv4 = IPv4Address::fromIPv6($ipv6);
        $this->assertEquals("\x7F\x00\x00\x01", $ipv4->toBinary());
    }

    public function testGetClassOf()
    {
        $ips = [
            "A" => "\x7C\xFF\xDF\x01",
            "B" => "\x84\xD3\x12\x66",
            "C" => "\xC2\x44\x1D\xF4",
            "D" => "\xE4\xD7\x2E\x02",
            "E" => "\xF3\x01\xFF\x13",
        ];

        foreach ($ips as $class => $binary) {
            $ip = IPv4Address::fromBinary($binary);
            $this->assertEquals($class, $ip->getIPClass());
        }
    }

    public function testGetHighOrderBitsOfIPv4Class()
    {
        $this->assertEquals('0', IPv4Address::getHighOrderBitsOfIPv4Class('A'));
        $this->assertEquals('10', IPv4Address::getHighOrderBitsOfIPv4Class('B'));
        $this->assertEquals('110', IPv4Address::getHighOrderBitsOfIPv4Class('C'));
        $this->assertEquals('1110', IPv4Address::getHighOrderBitsOfIPv4Class('D'));
        $this->assertEquals('1111', IPv4Address::getHighOrderBitsOfIPv4Class('E'));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetHighOrderBitsOfIPv4ClassException()
    {
        IPv4Address::getHighOrderBitsOfIPv4Class('F');
    }

    public function testToString()
    {
        $ip = IPv4Address::fromBinary("\x7F\x00\x00\x01");
        $this->assertEquals('127.0.0.1', $ip->toString());

        $this->assertEquals($ip->toString(), (string) $ip);

    }
}
