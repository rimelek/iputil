ARG PHP_VERSION="8.2"
ARG VARIANT="cli"

FROM php:5.6-${VARIANT}-alpine as stage-5.6

RUN unlink /etc/ssl/cert.pem \
 && ln -s /etc/ssl/certs/ca-certificates.crt /etc/ssl/cert.pem  \
 && rm -rf /etc/ssl/certs /usr/share/ca-certificates

COPY --from=alpine:3.18 /etc/ssl/certs /etc/ssl/certs

FROM php:7.0-${VARIANT}-alpine as stage-7.0

RUN unlink /etc/ssl/cert.pem \
 && ln -s /etc/ssl/certs/ca-certificates.crt /etc/ssl/cert.pem  \
 && rm -rf /etc/ssl/certs /usr/share/ca-certificates

COPY --from=alpine:3.18 /etc/ssl/certs /etc/ssl/certs

FROM php:7.1-${VARIANT}-alpine as stage-7.1
FROM php:7.2-${VARIANT}-alpine as stage-7.2
FROM php:7.3-${VARIANT}-alpine as stage-7.3
FROM php:7.4-${VARIANT}-alpine as stage-7.4
FROM php:8.0-${VARIANT}-alpine as stage-8.0
FROM php:8.1-${VARIANT}-alpine as stage-8.1
FROM php:8.2-${VARIANT}-alpine as stage-8.2

FROM stage-${PHP_VERSION}

ARG XDEBUG_VERSION="3.2.0"

# linux-headers is required in php:8.0-alpine
RUN apk add --no-cache autoconf g++ make linux-headers \
    && yes | pecl install "xdebug-$XDEBUG_VERSION" \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini