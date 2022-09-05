# OpenTelemetry auto-instrumentation extension

This is an _experimental_ extension for OpenTelemetry, to enable auto-instrumentation.
It is based on [zend_observer](https://www.datadoghq.com/blog/engineering/php-8-observability-baked-right-in/) and requires php8+

## Building the extension

### With PECL
```shell
$ phpize
$ ./configure --with-php-config=/usr/local/bin/php-config
$ make
$ make install
```

This will build `otel_instrumentation.so` (and install it into php modules dir).

### With install-php-extensions

See https://github.com/mlocati/docker-php-extension-installer#installing-from-source-code

```shell
$ install-php-extensions /path/to/extension/source
```

You can also install straight from github:

```shell
$ install-php-extensions Nevay/opentelemetry-extension@main
```

## Enabling the extension

```shell
$ php -dextension=otel_instrumentation -m
```

Or via .ini:
```shell
echo 'extension=otel_instrumentation' > /usr/local/etc/php/conf.d/otel_instrumentation.ini
```

If the extension is successfully installed, you will see it listed in the output of `php -m`.

# Usage

Basic usage is in the `tests/` directory.

A more advanced example: https://github.com/open-telemetry/opentelemetry-php-contrib/pull/78/files

# Further reading

* https://www.phpinternalsbook.com/php7/build_system/building_extensions.html