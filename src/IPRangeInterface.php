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
     * C.I.D.R. Prefix
     *
     * @return int|null
     */
    public function getCIDRPrefix();

    /**
     * min - max
     *
     * @return string
     */
    public function toString();

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
    public function toCIDRNotations();
}