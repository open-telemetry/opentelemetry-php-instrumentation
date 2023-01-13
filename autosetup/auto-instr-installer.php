<?php

// This simple CLI script installs and setup
// everything what's needed for auto-instrumentation.

$dependencies = array(
  "php-http/guzzle7-adapter"
);

$opentelemetry_packages = array(
  "open-telemetry/sdk", 
  "open-telemetry/api",
  "open-telemetry/sdk-contrib",
  "open-telemetry/opentelemetry-auto-slim",
  "open-telemetry/opentelemetry-auto-psr15",
  "open-telemetry/opentelemetry-auto-psr18",
);

function usage() {
  echo ("usage : php auto-instr-installer.php [default | advanced]\n");
}

function check_args($argc, $argv):string {
  if ($argc != 2) {
    usage();
    exit(1);
  }
  if ($argv[1] != "default" && $argv[1] != "advanced") {
    usage();
    exit(1);
  }
  return $argv[1];
}

function get_php_async_client_impl(): array {
  $http_async_client_providers = "https://packagist.org/providers/php-http/async-client-implementation.json";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $http_async_client_providers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);  
  $json = json_decode($output);
  return $json->{"providers"};
}

// There are 3 preconditions 
// - installed php engine
// - installed composer
// - installed picke
function check_preconditions() {

}

function set_env() {
  // putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
  // putenv('OTEL_TRACES_EXPORTER=zipkin');
  // putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
  // putenv('OTEL_METRICS_EXPORTER=otlp');
  // putenv('OTEL_EXPORTER_OTLP_METRICS_PROTOCOL=grpc');
  // putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:9411/api/v2/spans');
  // putenv('OTEL_EXPORTER_ZIPKIN_ENDPOINT=http://localhost:9411/api/v2/spans');
  // putenv('OTEL_PHP_TRACES_PROCESSOR=simple');
  // putenv('OTEL_SERVICE_NAME=autoloading');
}

function colorLog($str, $type = 'i'){
  switch ($type) {
      case 'e': //error
          echo "\033[31m$str \033[0m\n";
      break;
      case 's': //success
          echo "\033[32m$str \033[0m\n";
      break;
      case 'w': //warning
          echo "\033[33m$str \033[0m\n";
      break;  
      case 'i': //info
          echo "\033[36m$str \033[0m\n";
      break;      
      default:
      # code...
      break;
  }
}

function dump_output($output) {
  foreach ($output as $v) {
    colorLog($v, 'e');
  }
}

function execute_command(string $cmd) {
  $output = array();
  $result_code = null;
  colorLog($cmd);
  exec($cmd . " 2>&1", $output, $result_code);
  if ($result_code > 0) {
    dump_output($output);
    exit($result_code);
  }
}

function make_composer_require_command($package_name, $version, $options) {
  return "composer require " . $package_name . $version . " " . $options;
}

function make_pickle_install($repo, $version, $options) {
  return "pickle install --source " . $repo . $version . " " . $options;
}

function make_composer_config_command($param, $options) {
  return "composer config " . $param . " " . $options;
}

function make_default_setup($dependencies, $packages) {
  execute_command(make_composer_config_command(
    "minimum-stability dev",
    ""));

    foreach ($dependencies as $dep) {
      execute_command(make_composer_require_command(
        $dep, 
        "", 
        "--with-all-dependencies"));
    }

    foreach ($packages as $package) {
      execute_command(make_composer_require_command(
        $package, 
        "", 
        "--with-all-dependencies"));
    }
    execute_command(make_pickle_install(
      "https://github.com/open-telemetry/opentelemetry-php-instrumentation.git",
      "#main", ""));
}

$mode = check_args($argc, $argv);
if ($mode == "default") {
  make_default_setup($dependencies, $opentelemetry_packages);
} else {
  $providers = get_php_async_client_impl();
  foreach ($providers as $provider) {
    var_dump($provider->name);
  }  
}
