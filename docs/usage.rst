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

Let's see how to convert addresses

.. code-block:: php

    <?php
    use Rimelek\IPUtil\IPv4Address;

    // from IPv4 binary string to string
    $ip4 = IPv4Address::fromBinary("\x7f\x00\x00\x01");
    echo $ip4->toString();
    // output: 127.0.0.1

    // from IPv4 string to bit string
    $ip4 = IPv4Address::fromBinary("127.0.0.1");
    echo $ip4->toBitString();
    // output: 01111111000000000000000000000001

    $ip4 = IPv4Address::fromBitString("01111111000000000000000000000001");
    $binary = $ip4->toBinary();
    // It is not printable so do not use it to show an IP address

You can do the same with IPv6

.. code-block:: php

    <?php
    use Rimelek\IPUtil\IPv6Address;

    // from IPv6 binary string to string
    $ip6 = IPv4Address::fromBinary("\0\0\0\0\0\0\0\0\0\0\xFF\xFF\x7F\x00\x00\x01");
    echo $ip6->toString(); // This is an alias of $ipv6->toString(IPv6Address::LENGTH_SHORT);
    // output: ::ffff:7f00:1
    echo $ip6->toString(IPv6Address::LENGTH_MEDIUM);
    // output: 0:0:0:0:0:ffff:7f00:1
    echo $ip6->toString(IPv6Address::LENGTH_LONG);
    // output: 0000:0000:0000:0000:0000:ffff:7f00:0001

    // from IPv6 string to bit string
    $ip6 = IPv6Address::fromBinary("::ffff:7f00:1");
    echo $ip6->toBitString();
    // output: 00000000000000000000000000000000000000000000000000000000000000000000000000000000111111111111111101111111000000000000000000000001

    $ip6 = IPv6Address::fromBitString("00000000000000000000000000000000000000000000000000000000000000000000000000000000111111111111111101111111000000000000000000000001");
    $binary = $ip6->toBinary();
    // It is not printable so do not use it to show an IP address

And you can convert an IPv4 address to IPv6 or IPv6 address to IPv4

.. code-block:: php

    <?php
    // ...
    $ip6 = $ip4->toIPv6();
    $ip4 = $ip6->toIPv4();

or

.. code-block:: php

    <?php
    // ...
    $ip6 = IPv6Address::fromIPv4($ip4);
    $ip4 = IPv4Address::fromIPv6($ip6);

CIDR prefix is a number that tells you how many bits are set in the IP mask from left to right followed by zeros only.
If you need IP mask, you can create it from CIDR prefix.

.. code-block:: php

    <?php
    // ...
    $ip4Mask = IPv4Address::fromCIDRPrefix(24);
    echo $ip4Mask->toString();
    // output: 255.255.255.0

    $ip6Mask = IPv6Address::fromCIDRPrefix(40);
    echo $ip6Mask->toString();
    // output: ffff:ffff:ff00::

Not all IPv6 addresses are compatible with IPv4. If you do not want to get an exception when you call $ip6->toIPv4(),
use isCompatibleWithIPv4() to check if it is compatible.

.. code-block:: php

    <?php
    // ...
    if ($ip6->isCompatibleWithIPv4()) {
        $ip4 = $ip6->toIPv4();
    }

You can get some additional information about an IPv4 address like IP class and high order bits

.. code-block:: php

    <?php
    // ...
    $ip4 = IPv4Address::fromString('192.168.1.1');
    echo $ip4->getIPClass();
    // output: C

The above check based on high order bits. You can get the high order bits of an IP class by calling getHighOrderBitsOfIPv4Class()


.. code-block:: php

    <?php
    // ...
    echo IPv4Address::getHighOrderBitsOfIPv4Class('C');
    // output: 110

IP ranges
---------

You can create an IP range instance three way. Directly giving minimum and maximum IP address instances or
binary strings or using one IP address instance and a CIDR prefix.

.. code-block:: php

    <?php
    // ...
    $ip4min = IPv4Address::fromString('192.168.1.0');
    $ip4max = IPv4Address::fromString('192.168.1.255');

    $ip4Range = IPv4Range::fromIPInterval($ip4min, $ip4max);
    // or
    $ip4range = IPv4Address::fromBinaryInterval("\xC0\xA8\x01\x00", "\xC0\xA8\x01\xFF");
    // or
    $ip4Range = IPv4Range::fromIPWithCIDRPrefix($ip4min, 24);

Third way you do not even need to pass the minimum address.
An address between minimum and maximum is appropriate.

    <?php
    // ...
    $ip4min = IPv4Address::fromString('192.168.1.18');
    $ip4Range = IPv4Range::fromIPWithCIDRPrefix($ip4min, 24);

IPv6Range works the same way with IPv6Address of course.

You can always get the minimum and maximum IP addresses:

.. code-block:: php

    <?php
    echo $ip4Range->getMinIP()->toString();
    echo " - ";
    echo $ip4Range->getMaxIP()->toString();
    // output: 192.168.1.0 - 192.168.1.255

Currently this is the same as calling toString() on an IP range instance.

.. code-block:: php

    <?php
    echo $ip4Range->toString();

This only for testing and debugging. Do not use it to check if two ranges are equal!

If a range was instantiated by fromIPWithCIDRPrefix(), you get the prefix at any time.
Otherwise, it will be null.

.. code-block:: php

    <?php
    echo $ip4Range->getCIDRPrefix();

You can check if a range is in an other:

.. code-block:: php

    <?php
    if ($ip4Range->in($largerIp4Range)) {
        echo 'largerIP4Range is in ip4Range';
    }

When a range was created by fromIPInterval() or fromBinaryInterval(), converting it to one CIDR notation
is not always possible. Use toCIDRNotations() on the instance of the range. This returns an array with
new IP range instances. They are all created by fromIPWithCIDRPrefix()

.. code-block:: php

    <?php
    $ip4min = IPv4Address::fromString('192.168.1.0');
    $ip4max = IPv4Address::fromString('192.168.2.0');

    $ip4Range = IPv4Range::fromIPInterval($ip4min, $ip4max);
    foreach ($ip4Range->toCIDRNotations() as $CIDRNotation) {
        echo $CIDRNotation->getMinIP() . '/' . $CIDRNotation->getCIDRPrefix() . "\n";
    }
    // output:
    // 192.168.1.0/24
    // 192.168.2.0/32
