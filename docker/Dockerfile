ARG PHP_VERSION=8.1
FROM php:${PHP_VERSION}
WORKDIR /usr/src/myapp
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions \
  && apt-get update

RUN apt-get install -y \
    autoconf \
    automake \
    libtool \
    make \
    gcc \
    g++
