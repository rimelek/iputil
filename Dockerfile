ARG PHP_VERSION="7.2"

FROM php:${PHP_VERSION}-alpine

ARG XDEBUG_VERSION="3.1.6"

# linux-headers is required in php:8.0-alpine
RUN apk add --no-cache autoconf g++ make linux-headers \
    && yes | pecl install "xdebug-$XDEBUG_VERSION" \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini