# Setting up the build environment

By default, an alpine and debian-based docker image with debug enabled is used.

```shell
$ PHP_VERSION=x.y.z docker compose build debian
# or
$ PHP_VERSION=x.y.z docker compose build alpine
```

You can add extra configure flags, but some may require extra dependencies to be installed.

You can also change the PHP version:

```shell
$ docker compose build --build-arg PHP_CONFIG_OPTS="--enable-debug --enable-zts" --build-arg PHP_VERSION=8.0.23 [debian|alpine]
```

The latest PHP version can be found on: https://www.php.net/releases/index.php

# Building the extension

First, shell into the container:
```shell
$ docker compose run debian
```

## Using pear/pecl

```shell
$ pear build
```

## From source code
```shell
$ phpize
$ ./configure
$ make
$ make test
$ make install
$ make clean
```

This will build `opentelemetry.so` and install it into php modules dir (but not enable it).

To clean up, especially between builds with different PHP versions and/or build options:

```shell
$ git clean -Xn
# looks ok? then
$ git clean -Xf
```

or `make clean` from in the container.

# Enabling the extension

```shell
$ php -dextension=opentelemetry -m
```

Or via .ini:
```shell
$ echo 'extension=opentelemetry' > $(php-config --ini-dir)/opentelemetry.ini
```

If the extension is successfully installed, you will see it listed in the output of `php -m`.

# Code formatting
Run `make format` before committing changes, which will run `clang-format -i *.c *.h`.

# Debugging

## Locally

In order to debug extension, php engine has to be compiled in debug mode. All needed steps
are described on the following page: https://www.zend.com/resources/php-extensions/setting-up-your-php-build-environment
Above page describes building environment for linux. On other systems (like MacOS), some additional steps might be
needed related to installing and configuring some dependencies.

Next is to add extension to ini file as described above.

After finishing all above steps, you can start a debugging session. There are few debuggers that
can be used for debugging process, lldb, gdb or visual studio debugger on windows.
To trigger auto instrumentation code you need to invoke observer api functionality.
Following, very simple script can be used as reference example, created and saved as test.php
(this name will be referenced later during debugger invocation):

```shell
<?php
$ret = \OpenTelemetry\Instrumentation\hook(null, 'some_function');
var_dump($ret);
?>
```

Now, you can invoke lldb with following arguments:
```shell
lldb -- $HOME/php-bin/DEBUG/bin/php test.php
```

For gdb, arguments look very similar:
```shell
gdb --args $HOME/php-bin/DEBUG/bin/php test.php
```

and set breakpoint in function like `opentelemetry_observer_init` just to test if debugger will
stop there by:
```shell
b opentelemetry_observer_init
```

## Docker

You can use docker + compose to debug the extension.

Run all tests:
```shell
PHP_VERSION=8.2.8 docker compose build debian
PHP_VERSION=8.2.8 docker compose run debian
phpize
./configure
make clean
make
make test
```

The docker image has gdb and valgrind installed, to enable debugging and memory-leak checking.

Run all tests with valgrind:
```shell
php run-tests.php -d extension=$(pwd)/modules/opentelemetry.so -m
```

Run one test with valgrind:
```shell
php run-tests.php -d extension=$(pwd)/modules/opentelemetry.so -m tests/<name>.phpt
```

If any tests fail, a `.sh` script is created which you can use
to run the test in isolation, and optionally with `gdb` or `valgrind`:

```shell
tests/name_of_test.sh gdb # will start gdb
tests/name_of_test.sh valgrind # will run test and display valgrind report
```

Further reading: https://www.phpinternalsbook.com/php7/memory_management/memory_debugging.html#debugging-memory

### gdbserver

To debug tests running in a docker container, you can use `gdbserver`:

```shell
docker build --build-arg PHP_VERSION=8.2.11 -f docker/Dockerfile.debian . -t otel:8.2.11
docker run --rm -it -p "2345:2345" -v $(pwd)/ext:/usr/src/myapp otel:8.2.11 bash
```

Then, inside the container:

```shell
phpize
./configure
make
gdbserver :2345 php -d extension=$(pwd)/modules/opentelemetry.so /path/to/file.php
```

Now, gdbserver should be running and awaiting a connection. Configure your IDE to connect via
`gdb` to `127.0.0.1:2345` and start debugging, which should connect to the waiting server
and start execution.

# Packaging for PECL

See https://github.com/opentelemetry-php/dev-tools#pecl-release-tool

# Usage

Basic usage is in the `tests/` directory.

A more advanced example: https://github.com/open-telemetry/opentelemetry-php-contrib/pull/78/files

# Further reading

* https://www.phpinternalsbook.com/php7/build_system/building_extensions.html
