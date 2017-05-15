Introduction
============

The goal of the library
-----------------------

Sometimes you need to work with IP addresses. For example you want to restrict access to a website,
but the restriction depends on a list of IP addresses from a database or other sources. Imagine you are
a developer in an institution that has a large network with many subnet or individual PCs.
You want to make sure the service is available only for coworkers in the subnets of the institution
even if the server is accessible publicly. So anyone can manage the addresses in the database and set
a minimum and maximum IP addresses of one or many IP ranges and you can provide an api to get CIDR
notations or find overlapping IP ranges and fix them.

This is the goal of the library. To make it simple and give you a tool to work with IPv4 and IPv6
addresses.

Requirements
------------

- PHP 5.6
- ...