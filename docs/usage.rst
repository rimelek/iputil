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
    // output: 0000000000000000000000000000000000000000111111111111111101111111000000000000000000000001

    $ip6 = IPv6Address::fromBitString("0000000000000000000000000000000000000000111111111111111101111111000000000000000000000001");
    $binary = $ip4->toBinary();
    // It is not printable so do not use it to show an IP address

And you can convert an IPv4 address to IPv6 or IPv6 address to IPv4

.. code-block:: php

    <?php
    // ...
    $ip6 = $ip4->toIPv6();
    $ip4 = $ip6->toIPv4();


**This page is not complete yet! More examples are coming soon.**