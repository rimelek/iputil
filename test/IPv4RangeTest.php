<?php

namespace Rimelek\IPUtil\Test;

use Rimelek\IPUtil\IPv4Address;
use Rimelek\IPUtil\IPv4RangeAbstract;
use PHPUnit\Framework\TestCase;

class IPv4RangeTest extends TestCase
{
    public function dataProviderFromIPWithCIDRPrefix()
    {
        return [
            ["\x7F\x00\x00\x01", 32, "\x7F\x00\x00\x01", "\x7F\x00\x00\x01"],
            ["\x7F\x00\x00\x01", 1, "\x00\x00\x00\x00", "\x7F\xFF\xFF\xFF"],
            ["\x80\x00\x00\x01", 1, "\x80\x00\x00\x00", "\xFF\xFF\xFF\xFF"],
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
        $ip = IPv4Address::fromBinary($binary);
        $range = IPv4RangeAbstract::fromIPWithCIDRPrefix($ip, $prefix);
        $this->assertEquals($minBinary, $range->getMinIP()->toBinary());
        $this->assertEquals($maxBinary, $range->getMaxIP()->toBinary());

        $this->assertEquals($prefix, $range->getCIDRPrefix());
    }

    public function dataProviderToCIDRPrefixedRanges()
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
            ]],
            ["\x00\x00\x00\xFF", "\x00\x00\x01\x01", [
                ["\x00\x00\x00\xFF", "\x00\x00\x00\xFF"],
                ["\x00\x00\x01\x00", "\x00\x00\x01\x01"],
            ]],
            ["\x00\x00\x00\xFE", "\x00\x00\x01\x00", [
                ["\x00\x00\x00\xFE", "\x00\x00\x00\xFF"],
                ["\x00\x00\x01\x00", "\x00\x00\x01\x00"],
            ]]
        ];
    }

    /**
     *
     * @dataProvider dataProviderToCIDRPrefixedRanges
     * @param $from
     * @param $to
     * @param array $expected
     */
    public function testToCIDRPrefixedRanges($from, $to, $expected)
    {
        $ranges = IPv4RangeAbstract::fromBinaryInterval($from, $to)->toCIDRPrefixedRanges();

        if (count($expected) === count($ranges)) {
            foreach ($ranges as $i => $range) {
                $this->assertEquals($range->getMinIP()->toBinary(), $expected[$i][0]);
                $this->assertEquals($range->getMaxIP()->toBinary(), $expected[$i][1]);
            }
        } else {
            //var_dump($expected, $ranges);
            $this->fail('The size of calculated ranges and expected ranges are different: Expected: ' . count($expected) . ', Actual: ' . count($ranges));
        }
    }
}
