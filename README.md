# OpenTelemetry auto-instrumentation extension

This is an _experimental_ extension for OpenTelemetry, to enable auto-instrumentation.
It is based on [zend_observer](https://www.datadoghq.com/blog/engineering/php-8-observability-baked-right-in/) and requires php8+

## Building the build environment

By default, an alpine and debian-based docker image with debug enabled is built.

```shell
$ docker-compose build debian
# or
$ docker-compose build alpine
```

You can add extra configure flags, but some may require extra dependencies to be installed.

You can also change the PHP version:

```shell
$ docker-compose build --build-arg PHP_CONFIG_OPTS="--enable-debug --enable-zts" --build-arg PHP_VERSION=8.0.23 [debian|alpine]
```

## Building the extension

First, shell into the container:
```shell
$ docker-compose run debian
```

### With PECL
```shell
$ phpize
$ ./configure
$ make
$ make test
$ make install
$ make clean
```

This will build `otel_instrumentation.so` and install it into php modules dir (but not enable it).

To clean up, especially between builds with different PHP versions and/or build options:

```shell
$ git clean -Xn
# looks ok? then
$ git clean -Xf
```

or `make clean` from in the container.

### With install-php-extensions

_n.b that this will strip debug symbols_

See https://github.com/mlocati/docker-php-extension-installer#installing-from-source-code

```shell
$ install-php-extensions $(pwd)
```

You can also install straight from github:

```shell
$ install-php-extensions Nevay/opentelemetry-extension@master
```

## Enabling the extension

```shell
$ php -dextension=otel_instrumentation -m
```

Or via .ini:
```shell
$ echo 'extension=otel_instrumentation' > $(php-config --ini-dir)/otel_instrumentation.ini
```

If the extension is successfully installed, you will see it listed in the output of `php -m`.

# Usage

Basic usage is in the `tests/` directory.

A more advanced example: https://github.com/open-telemetry/opentelemetry-php-contrib/pull/78/files

# Further reading

* https://www.phpinternalsbook.com/php7/build_system/building_extensions.html