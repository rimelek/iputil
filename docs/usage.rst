Usage and examples
==================

IP conversions
--------------

IP addresses are stored in the objects as binary strings, but there are other formats you can
convert to and from. Here are all off them:

- **binary string**: IPv4 addresses are 4, IPv6 addresses are 16 bytes long strings.
  This is the best format to work with them and the worst to show them.
  You cannot just echo a binary string. You need to use
  `unpack() <http://php.net/manual/en/function.unpack.php>`_ for example.
  You can however create a binary string using hex numbers: "\\x7F\\x00\\x00\\x01".
  It is the binary version of "127.0.0.1"

- **bit string**: It is made of series of 1 and 0.

- **string**: Well, each version is a string, but this is the general representation of an
  IP address which is for example 127.0.0.1 or ::FFFF:7F00:1.

First we need the autoloader

.. code-block:: php

    <?php
    require_once 'vendor/autoload.php';
  
Let's see how to convert addresses

.. code-block:: php

    <?php
    // ...
    use Rimelek\IPUtil\IPv4Address;

    // from IPv4 binary string to string
    $ip4 = IPv4Address::fromBinary("\x7f\x00\x00\x01");
    echo $ip4->toString() . "\n";
    // output: 127.0.0.1

    // from IPv4 string to bit string
    $ip4 = IPv4Address::fromBinary("127.0.0.1");
    echo $ip4->toBitString() . "\n";
    // output: 01111111000000000000000000000001

    $ip4 = IPv4Address::fromBitString("01111111000000000000000000000001");
    echo bin2hex($ip4->toBinary()) . "\n";

You can do the same with IPv6

.. code-block:: php

    <?php
    // ..
    use Rimelek\IPUtil\IPv6Address;

    // from IPv6 binary string to string
    $ip6 = IPv4Address::fromBinary("\0\0\0\0\0\0\0\0\0\0\xFF\xFF\x7F\x00\x00\x01");
    echo $ip6->toString() . "\n"; // This is an alias of $ipv6->toString(IPv6Address::LENGTH_SHORT);
    // output: ::ffff:7f00:1
    echo $ip6->toString(IPv6Address::LENGTH_MEDIUM) . "\n";
    // output: 0:0:0:0:0:ffff:7f00:1
    echo $ip6->toString(IPv6Address::LENGTH_LONG) . "\n";
    // output: 0000:0000:0000:0000:0000:ffff:7f00:0001

    // from IPv6 string to bit string
    $ip6 = IPv6Address::fromBinary("::ffff:7f00:1");
    echo $ip6->toBitString() . "\n";
    // output: 00000000000000000000000000000000000000000000000000000000000000000000000000000000111111111111111101111111000000000000000000000001

    $ip6 = IPv6Address::fromBitString("00000000000000000000000000000000000000000000000000000000000000000000000000000000111111111111111101111111000000000000000000000001");
    echo bin2hex($ip6->toBinary()) . "\n";
    // output: 00000000000000000000ffff7f000001

And you can convert an IPv4 address to IPv6 or IPv6 address to IPv4

.. code-block:: php

    <?php
    // ...
    $ip4 = IPv4Address::fromString('192.168.1.1');
    $ip6 = $ip4->toIPv6();
    echo $ip6->toString() . "\n";
    // output: ::ffff:c0a8:101

    $ip4 = $ip6->toIPv4();
    echo $ip4->toString() . "\n";
    // output: ::ffff:c0a8:101

or

.. code-block:: php

    <?php
    // ...
    $ip6 = IPv6Address::fromIPv4($ip4);
    echo $ip6->toString() . "\n";
    // output: ::ffff:c0a8:101

    $ip4 = IPv4Address::fromIPv6($ip6);
    echo $ip4->toString() . "\n";
    // output: ::ffff:c0a8:101

CIDR prefix is a number that tells you how many bits are set in the IP mask from left to right followed by zeros only.
If you need IP mask, you can create it from CIDR prefix.

.. code-block:: php

    <?php
    // ...
    $ip4Mask = IPv4Address::fromCIDRPrefix(24);
    echo $ip4Mask->toString() . "\n";
    // output: 255.255.255.0

    $ip6Mask = IPv6Address::fromCIDRPrefix(40);
    echo $ip6Mask->toString() . "\n";
    // output: ffff:ffff:ff00::

Not all IPv6 addresses are compatible with IPv4. If you do not want to get an exception when you call $ip6->toIPv4(),
use isCompatibleWithIPv4() to check if it is compatible.

.. code-block:: php

    <?php
    // ...
    $ip6 = IPv6Address::fromString('2620:2d:4000:1::16');
    echo $ip6->isCompatibleWithIPv4() ? 'Compatible' : 'Incompatible';
    echo "\n";
    // output: Incompatible

    $ip6 = IPv6Address::fromString('::ffff:c0a8:101');
    echo $ip6->isCompatibleWithIPv4() ? 'Compatible' : 'Incompatible';
    echo "\n";
    // output: Compatible

You can get some additional information about an IPv4 address like IP class and high order bits

.. code-block:: php

    <?php
    // ...
    $ip4 = IPv4Address::fromString('192.168.1.1');
    echo $ip4->getIPClass() . "\n";
    // output: C
    $ip4 = IPv4Address::fromString('172.17.1.1');
    echo $ip4->getIPClass() . "\n";
    // output: B

The above check based on high order bits. You can get the high order bits of an IP class by calling getHighOrderBitsOfIPv4Class()


.. code-block:: php

    <?php
    // ...
    echo IPv4Address::getHighOrderBitsOfIPv4Class('C') . "\n";
    // output: 110

IP ranges
---------

You can create an IP range instance three ways. Directly giving minimum and maximum IP address instances or
binary strings or using one IP address instance and a CIDR prefix.

.. code-block:: php

    <?php
    // ...
    use Rimelek\IPUtil\IPv4Range;
    // ...
    $ip4min = IPv4Address::fromString('192.168.1.0');
    $ip4max = IPv4Address::fromString('192.168.1.255');

    $ip4Range = IPv4Range::fromIPInterval($ip4min, $ip4max);
    echo $ip4Range->toString() . "\n";
    // output: 192.168.1.0 - 192.168.1.255

    $ip4range = IPv4Range::fromBinaryInterval("\xC0\xA8\x01\x00", "\xC0\xA8\x01\xFF");
    echo $ip4Range->toString() . "\n";
    // output: 192.168.1.0 - 192.168.1.255

    $ip4Range = IPv4Range::fromIPWithCIDRPrefix($ip4min, 24);
    echo $ip4Range->toString() . "\n";
    // output: 192.168.1.0 - 192.168.1.255

Using the CIDR notation you do not even need to pass the minimum address.
An address between minimum and maximum is appropriate.

.. code-block:: php

    <?php
    // ...
    $ip4min = IPv4Address::fromString('192.168.1.18');
    $ip4Range = IPv4Range::fromIPWithCIDRPrefix($ip4min, 24);
    echo $ip4Range->toString() . "\n";
    // output: 192.168.1.0 - 192.168.1.255

IPv6Range works the same way with IPv6Address of course.

You can also get the minimum and maximum IP addresses without :code:`toString()`:

.. code-block:: php

    <?php
    // ...
    echo $ip4Range->getMinIP()->toString();
    echo " - ";
    echo $ip4Range->getMaxIP()->toString();
    // output: 192.168.1.0 - 192.168.1.255

This only for testing and debugging. Do not use it to check if two ranges are equal!

If a range was instantiated by :code:`fromIPWithCIDRPrefix()`, you can get the prefix any time.
Otherwise, it will be null.

.. code-block:: php

    <?php
    // ...
    echo $ip4Range->getCIDRPrefix() . "\n";
    // output: 24

You can check if a range is in another range:

.. code-block:: php

    <?php
    // ...
    if ($ip4Range->in($largeIP4Range)) {
        echo 'ip4Range is in largerIp4Range';
    }

When a range was created by :code:`fromIPInterval()` or :code:`fromBinaryInterval()`, converting it to one CIDR notation
is not always possible. Use :code:`toCIDRNotations()` on the instance of the range. This returns an array with
new IP range instances. They are all created by :code:`fromIPWithCIDRPrefix()`

.. code-block:: php

    <?php
    $ip4min = IPv4Address::fromString('192.168.0.3');
    $ip4max = IPv4Address::fromString('192.168.2.18');

    $ip4Range = IPv4Range::fromIPInterval($ip4min, $ip4max);
    foreach ($ip4Range->toCIDRNotations() as $CIDRNotation) {
        echo $CIDRNotation->getMinIP() . '/' . $CIDRNotation->getCIDRPrefix() . "\n";
    }
    // output:
    // 192.168.0.3/32
    // 192.168.0.4/30
    // 192.168.0.8/29
    // 192.168.0.16/28
    // 192.168.0.32/27
    // 192.168.0.64/26
    // 192.168.0.128/25
    // 192.168.1.0/24
    // 192.168.2.0/28
    // 192.168.2.16/31
    // 192.168.2.18/32
