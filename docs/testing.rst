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

- **./php/bin/php-`<PHP_VERSION>.sh`**: Wrapper scripts for :code:`php.sh` configured in PHPStorm as PHP interpreters.
- **./php/bin/php-all.sh**: A special interpreter which runs the command with each custom PHP interpreter.
  Using this script you can see the test result for each PHP version where the test suite name contains
  the version number.
- **./php/bin/phpunit.php**: This PHP script runs inside the container of the PHP interpreter and changes some of the
  parameters which was not handled in :code:`php.sh`. It is responsible for downloading a compatible version
  of PHPUnit as a PHP Archive (phar).


Then run the following command to test in terminal:

.. code-block:: bash

    ./php/bin/php-8.2.sh $PWD/php/bin/phpunit.php --configuration phpunit.xml

It actually doesn't matter what you pass as configuration file. It is just a placeholder so :code:`phpunit.php`
can replace it with the required and compatible configuration file.

At the end, you should see "100%" which means everything works well. If you are not sure every
line is tested, use code coverage. In this case xdebug extension must be installed.

.. code-block:: none

    php -dxdebug.coverage_enable=1 phpunit.phar --coverage-clover phpunit.coverage --configuration phpunit.xml

phpunit.coverage will contain the result of code coverage and IDEs can read it. The project contains
a custom, prepared PHP interpreter based on `Docker <https://www.docker.com/>`_. So you need to install
Docker at fist, then run a simple command to download the image and create the container. Any command is perfect
for this like:

.. code-block:: none

    chmod +x php.sh
    ./php.sh -v

Downloading the image and installing the necessary extensions can be slow. I will upload this image to the
Docker Hub, I promise! Until then, you need to be patient. When the previous command shows you the version of the
PHP, it is ready to test the library.

.. code-block:: none

    ./php.sh -dxdebug.coverage_enable=1 phpunit.phar --coverage-clover $PWD/phpunit.coverage --configuration phpunit.xml

$PWD is needed before phpunit.coverage, because Docker will mount its folder into the container and to do it,
it needs absolute path.

This custom interpreter was created to use with PHPStorm. PHPStorm supports to choose Docker container as
interpreter, but in this case, Code Coverage does not work with phpunit.phar and needs to be installed via composer.
I did not want to install it via composer, so I am the reason why everyone have to suffer :)

Of course, in your project, you are free to use composer.