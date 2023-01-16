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

// There are 2 preconditions
// - installed php engine
// - installed composer
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

function get_versions($output, $type = 'e'):array {
  $versions_array = array();
  foreach ($output as $line) {
    if (str_contains($line, "versions : ")) {
      $versions = substr($line, 0 + strlen("versions : "));
      $versions_array = explode(",", $versions);
      for($i = 0; $i < count($versions_array); ++$i) {
        $versions_array[$i] = trim($versions_array[$i], "* \n\r\t\v\x00");
      }
    }
  }
  return $versions_array;
}

function dump_output($output, $type = 'e') {
  foreach ($output as $v) {
    colorLog($v, $type);
  }
}

function execute_command(string $cmd, string $options) {
  $output = array();
  $result_code = null;
  colorLog($cmd);
  exec($cmd . $options, $output, $result_code);
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

function make_composer_show_command($package_name) {
  return "composer show -a " . $package_name . " -n";
}

function make_default_setup($dependencies, $packages) {
  execute_command(make_composer_config_command(
    "minimum-stability dev",
    ""), " 2>&1");

    foreach ($dependencies as $dep) {
      execute_command(make_composer_require_command(
        $dep, 
        "", 
        "--with-all-dependencies"), " 2>&1");
    }

    foreach ($packages as $package) {
      execute_command(make_composer_require_command(
        $package, 
        "", 
        "--with-all-dependencies"), " 2>&1");
    }
    execute_command(make_pickle_install(
      "https://github.com/open-telemetry/opentelemetry-php-instrumentation.git",
      "#main", ""), " 2>&1");
}

function choose_http_async_impl_provider($providers):int {
  $counter = 1;
  foreach ($providers as $provider) {
    echo($counter . ") " . $provider->name . "\n");
    ++$counter;
  }
  echo "\n";
  $provider_index = 0;
  do {
    $provider_index = intval(readline("Choose provider (1-" . count($providers) . "): "));
  } while ($provider_index == 0 && $provider_index < 1 || $provider_index > count($providers)) ;
  return $provider_index;
}

function choose_version($versions):int {
  $counter = 1;
  foreach ($versions as $v) {
    echo($counter . ") " . $v . "\n");
    ++$counter;
  } 

  echo "\n";
  $version_index = 0;
  do {
    $version_index = intval(readline("Choose version (1-" . count($versions) . "): "));
  } while ($version_index == 0 && $version_index < 1 || $version_index > count($versions)) ;
  return $version_index;

}

function make_advanced_setup($packages) {
  $providers = get_php_async_client_impl();
  echo "\nBelow is a list of http client async providers, you need to choose one:\n\n";
  $provider_index = choose_http_async_impl_provider($providers);
  execute_command(make_composer_config_command(
    "minimum-stability dev",
    ""), " 2>&1");
  execute_command(make_composer_require_command(
    $providers[$provider_index]->name,
    "",
    "--with-all-dependencies"), " 2>&1");
    foreach ($packages as $package) {
      $output = array();
      $result_code = null;
      $cmd = make_composer_show_command($package);
      colorLog($cmd);
      exec($cmd, $output, $result_code);
      $versions = get_versions($output, 'i');
      $version_index = choose_version($versions); 
      execute_command(make_composer_require_command(
        $package,
        ":" . $versions[$version_index - 1],
        "--with-all-dependencies"), " 2>&1");
  }
  execute_command(make_pickle_install(
    "https://github.com/open-telemetry/opentelemetry-php-instrumentation.git",
    "#main", ""), " 2>&1");

}

$mode = check_args($argc, $argv);

if ($mode == "default") {
  make_default_setup($dependencies, $opentelemetry_packages);
} else {
  make_advanced_setup($opentelemetry_packages);
}
