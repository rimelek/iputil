.. _testing:

============
Unit testing
============

Intro
=====

This library is fully unit-tested and does not have any special dependency, however, you can
run unit test if you wish. It is mainly useful when you fork the library and want to improve something.

The source code contains everything you need to run the tests, but it is optimized for using it in PHPStorm
with Docker. Using the custom Docker-based PHP interpreter and PHPUnit script you can test the source code
with multiple PHP versions and compatible PHPUnit versions.

These scripts are based on the following information:

- `Supported PHP versions by PHPUnit <https://phpunit.de/supported-versions.html>`_
- `Supported PHP versions by XDebug <https://xdebug.org/docs/compat>`_

PHP 5.6 is the oldest PHP version supported by the Library, but only because the project started in 2017
without releasing a stable version so I kept the compatibility in the first stable release and I will drop it
in the next release.

Requirements
============

Installing Docker is required if you want to use the custom PHP interpreters which are based on Docker containers.
You can follow the `official documentation <https://docs.docker.com/engine/install/>`_ to install Docker.
If you would like to read a short explanation about the variant of Docker check out the
`Install Docker <https://learn-docker.it-sziget.hu/en/latest/pages/intro/getting-started.html#install-docker>`_
in my "Learn Docker" tutorial.

Custom PHP interpreters
=======================

- **./php/bin/php.sh**: Wrapper script to run a container for any supported PHP version.
  It also contains some logic for running PHPUnit with customized configuration files and parameters.
  The first parameter of the script is the major and minor part of the PHP version and the rest of the parameters
  are the parameters of the PHP interpreter.

  .. code-block:: bash

    ./php/bin/php.sh 8.2 --version

- :code:`./php/bin/php-<PHP_VERSION>.sh`: Wrapper scripts for :code:`php.sh` configured in PHPStorm as PHP interpreters.
- :code:`./php/bin/php-all.sh`: A special interpreter which runs the command with each custom PHP interpreter.
  Using this script you can see the test result for each PHP version where the test suite name contains
  the version number.
- :code:`./php/bin/phpunit.php`: This file is just a placeholder which will never be used, but can be set
  in PHPStorm or other IDEs that require a phpunit script to be set.
  The :code:`php.sh` script will look for the path of this PHP file in the argument list
  to determine whether it is running PHPUnit or another PHP file.


Then run the following command to test in terminal:

.. code-block:: bash

    ./php/bin/php-8.2.sh "$PWD/php/bin/phpunit.php" --configuration phpunit.xml

It actually doesn't matter what you pass as configuration file. It is just a placeholder so :code:`phpunit.php`
can replace it with the required and compatible configuration file. :code:`phpunit.php` however must be written as
is, because the interpreter will compare it with an expected value to add PHPUnit-related arguments.

At the end, every line should be tested. If you are not sure if it happened, use code coverage.

.. code-block:: bash

  ./php/bin/php-8.2.sh "$PWD/php/bin/phpunit.php" --configuration phpunit.xml --coverage-clover "$PWD/phpunit.clover"

.. note::

  Starting the path of the coverage file with :code:`$PWD` or :code:`$(pwd)` is important,
  since it will be recognized by the custom php interpreters and mounted into the containers so as a
  bind mount, the source path must be absolute.

If you want to run coverage test using PHP 5.6, you need to add an extra parameter to enable code coverage.

.. code-block:: bash

    ./php/bin/php-5.6.sh -dxdebug.coverage_enable=1 "$PWD/php/bin/phpunit.php" --configuration phpunit.xml --coverage-clover "$PWD/phpunit.coverage.xml"

You can also use the PHPStorm GUI to run these tests, although PHP 5.6 unit test is not compatible with the latest
PHPStorm. You can see the result in the terminal of the unit test, but code coverage works with PHP 5.6 as well.

Downloading the base image and installing the necessary extensions can be slow. It can be a good idea to run a simple php
command before the actual test just for downloading the base images and building the local image:

.. code-block:: bash

  ./php/bin/php-all.sh --version

These custom interpreters was created to use with PHPStorm. PHPStorm supports to choose Docker container as
interpreter, but in that case, you would need much more manual configurations.
These custom interpreters can be set as local interpreters and will give the same output as any PHP interpreter would
do.

Depending on the PHP version `xdebug.remote_host` (PHP 7.1 and below) or `xdebug.client_host` (PHP 7.2 and above)
parameters can be set to send the debug data back to the right host. Optionally, you can create a `.env` file in the
project root and add 

.. code-block:: bash

  export XDEBUG_CLIENT_HOST=host.docker.internal

`host.docker.internal` is the default value too, but you cans et any IP addresses if you are not using Docker Desktop.


