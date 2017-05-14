<?php

namespace Rimelek\IPUtil\Test;

use Rimelek\IPUtil\AbstractIPRange;
use PHPUnit\Framework\TestCase;
use Rimelek\IPUtil\IPv4Address;
use Rimelek\IPUtil\IPv4RangeAbstract;
use Rimelek\IPUtil\IPv6RangeAbstract;

class IPRangeTest extends TestCase
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

    public function testToString()
    {
        $range = IPv4RangeAbstract::fromBinaryInterval(str_repeat("\0", 16), str_repeat("\xFF", 16));
        $this->assertEquals($range->toString(), (string) $range);
    }

    public function testCountFixBits()
    {
        $method = $this->getPrivateMethod(AbstractIPRange::class, 'countFixBits');
        $this->assertEquals(2, $method->invokeArgs(null, [
            '1100000',
            '1110000',
        ]));

        $this->assertEquals(1, $method->invokeArgs(null, [
            '1001000',
            '1110000',
        ]));

        $this->assertEquals(2, $method->invokeArgs(null, [
            '11001',
            '1110000',
        ]));
    }

    public function testIn()
    {
        $ip41 = IPv4Address::fromBinary("\x7F\x00\x00\x01");
        $ip42 = IPv4Address::fromBinary("\x7F\x00\x00\x02");
        $ip43 = IPv4Address::fromBinary("\x7F\x00\x00\x03");
        $ip44 = IPv4Address::fromBinary("\x7F\x00\x00\x04");

        $this->assertTrue(IPv4RangeAbstract::fromIPInterval($ip42, $ip43)->in(IPv4RangeAbstract::fromIPInterval($ip41, $ip44)));
        $this->assertTrue(IPv4RangeAbstract::fromIPInterval($ip42, $ip43)->in(IPv4RangeAbstract::fromIPInterval($ip41, $ip43)));
        $this->assertTrue(IPv4RangeAbstract::fromIPInterval($ip42, $ip43)->in(IPv4RangeAbstract::fromIPInterval($ip42, $ip44)));
        $this->assertTrue(IPv4RangeAbstract::fromIPInterval($ip42, $ip43)->in(IPv4RangeAbstract::fromIPInterval($ip42, $ip43)));

        $this->assertFalse(IPv4RangeAbstract::fromIPInterval($ip41, $ip44)->in(IPv4RangeAbstract::fromIPInterval($ip42, $ip43)));
        $this->assertFalse(IPv4RangeAbstract::fromIPInterval($ip41, $ip44)->in(IPv4RangeAbstract::fromIPInterval($ip41, $ip43)));
        $this->assertFalse(IPv4RangeAbstract::fromIPInterval($ip41, $ip44)->in(IPv4RangeAbstract::fromIPInterval($ip42, $ip44)));

        $this->assertTrue(IPv6RangeAbstract::fromIPInterval($ip42->toIPv6(), $ip43->toIPv6())->in(IPv4RangeAbstract::fromIPInterval($ip41, $ip44)));
        $this->assertTrue(IPv4RangeAbstract::fromIPInterval($ip42, $ip43)->in(IPv6RangeAbstract::fromIPInterval($ip41->toIPv6(), $ip44->toIPv6())));
    }
}
