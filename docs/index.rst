.. IPUtil documentation master file, created by
   sphinx-quickstart on Mon May 15 16:49:41 2017.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to IPUtil's documentation!
==================================

Your are reading the documentation of IPUtil which is a library written in PHP to help you
to handle IP addresses and IP ranges. It supports IPv4 and IPv6 addresses and IP ranges for
both versions. You can create IP address instances from multiple formats and then convert them
to other formats as well. Probably one of the most useful features is generating multiple CIDR notations
from any two IP addresses (lowest and highest) so you can use it in a software that requires
CIDR notations to restrict access to an interface and regenerate it any time using the library.

.. toctree::
   :maxdepth: 2
   :caption: Contents:

   intro
   install
   testing
   usage
