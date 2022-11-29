FROM debian:buster-slim as builder
WORKDIR /usr/src

RUN apt-get update -y \
  && apt-get install -y build-essential autoconf libtool bison re2c curl less pkg-config libxml2-dev libsqlite3-dev

ARG PHP_VERSION=8.1.10
ENV PHP_URL="https://www.php.net/distributions/php-${PHP_VERSION}.tar.xz"

RUN echo "$PHP_URL" && curl -fsSL -o php.tar.xz "$PHP_URL"
RUN cd /usr/src \
  && tar -xf php.tar.xz

ARG PHP_CONFIG_OPTS="--enable-debug"
RUN cd php-${PHP_VERSION} \
  && ./buildconf \
  && ./configure ${PHP_CONFIG_OPTS} \
  && make -j $(nproc) \
  && make install
WORKDIR /usr/src/myapp