<?php

namespace Rimelek\IPUtil;


interface IPRangeInterface
{
    /**
     *
     * @return IPAddressInterface
     */
    public function getMinIP();

    /**
     *
     * @return IPAddressInterface
     */
    public function getMaxIP();

    /**
     * Check if $this is a part of $range
     *
     * @param IPRangeInterface $range
     * @return bool
     */
    public function in(IPRangeInterface $range);

    /**
     * @return static[]
     */
    public function toCIDRPrefixedRanges();
}