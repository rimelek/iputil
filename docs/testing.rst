.. _testing:

Unit testing
============

This library is fully unit-tested and does not have any special dependency, however, you can
run unit test if you wish. It is mainly useful when you fork the library and want to improve something.

You will need

- `PHPUnit 6.1 <https://phpunit.de/>`_. Download it as phar file and save it into
the project root.
- PHP CLI >= 5.6

Then run the following command to test in terminal:

.. code-block:: none

    php phpunit.phar --configuration phpunit.xml

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