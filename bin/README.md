# Install auto instrumentation with one command.

This directory contains two scripts that helps install auto-instrumentation support and run application.

First script `install.php` will install all needed dependencies for auto-instrumentation. Second `run.php` is responsible for setting few environment variables (that are needed to export traces into backend) and running application.

Install script works in two modes:

- basic (will install defaults)
- advanced (interactive mode, you will control whole process)

## Example workflow

This section shows how to install and run auto-instrumented application which uses Slim framework.
To generate application, we follow steps described here: https://www.slimframework.com/.

```bash
    composer create-project slim/slim-skeleton:dev-master slimauto
    cd slimauto
    php [path to opentelemetry-php-instrumentation]\bin\install.php basic
    php [path to opentelemetry-php-instrumentation]\bin\run.php php -S localhost:8080 -t public public/index.php
```
