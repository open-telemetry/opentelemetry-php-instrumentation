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

$otel_traces_exporters = array(
  "otlp",
  "zipkin",
  "newrelic",
  "none"
);

$otel_exporter_otlp_protocols = array(
  "grpc",
  "http/protobuf",
  "http/json"
);

$otel_metrics_exporters = array(
  "otlp",
  "prometheus"
);

$otel_php_traces_procesors = array(
  "batch",
  "simple",
  "noop",
  "none"
);

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

function usage() {
  colorLog ("one-command-setup.php is a script that will help you", 'e');
  colorLog ("install and setup auto-instrumentation for your project.", 'e');
  colorLog ("It works in two modes default and advanced.", 'e');
  colorLog ("In default mode it will install everything using some defaults and latest", 'e');
  colorLog ("development versions. Advanced will ask you to choose needed packages and versions.\n", 'e');

  colorLog ("usage : php one-command-setup.php [default | advanced]\n", 'e');
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

function choose_element($elements, $default_index, $command_line):int {
  $counter = 1;
  foreach ($elements as $element) {
    echo($counter . ") " . $element . "\n");
    ++$counter;
  }
  echo "\n";
  $element_index = count($elements) - 1;
  do {
    $element_index = intval(readline("Choose " . $command_line . " (1-" . count($elements) . ") [" . $default_index . "] : "));
    if ($element_index == 0) {
      $element_index = $default_index;
      break;
    }
  } while ($element_index < 1 || $element_index > count($elements)) ;
  return $element_index - 1;
}

function choose_otel_traces_exporter($exporters):int {
  return choose_element($exporters, 1, "trace exporter");
}

function choose_otel_exporter_protocol($protocols):int {
  return choose_element($protocols, 1, "protocol");
}

function choose_otel_metrics_exporter($exporters):int {
  return choose_element($exporters, 1, "metrics exporter");
}

function choose_otel_php_traces_processor($traces_processors):int {
  return choose_element($traces_processors, 1, "traces processor");
}

function set_env($otel_traces_exporters,
                 $otel_exporter_otlp_protocols,
                 $otel_metrics_exporters,
                 $otel_php_traces_procesors) {
  $OTEL_PHP_AUTOLOAD_ENABLED = true;
  $val = "";
  do {
    $val = readline("set OTEL_PHP_AUTOLOAD_ENABLED=[true]: ");
    if ($val == "") {
      $val = "true";
      break;
    }
  } while ($val != "true" && $val != "false");
  $OTEL_PHP_AUTOLOAD_ENABLED = boolval($val);

  putenv('OTEL_PHP_AUTOLOAD_ENABLED=' . $OTEL_PHP_AUTOLOAD_ENABLED);
  echo "\n";

  $exporter_index = choose_otel_traces_exporter($otel_traces_exporters);
  putenv('OTEL_TRACES_EXPORTER=' . $otel_traces_exporters[$exporter_index]);
  echo "\n";

  $protocol_index = choose_otel_exporter_protocol($otel_exporter_otlp_protocols);
  putenv('OTEL_EXPORTER_OTLP_PROTOCOL=' . $otel_exporter_otlp_protocols[$protocol_index]);
  echo "\n";

  $metrics_exporter_index = choose_otel_metrics_exporter($otel_metrics_exporters);
  putenv('OTEL_METRICS_EXPORTER=' . $otel_metrics_exporters[$metrics_exporter_index]);
  echo "\n";

  $metrics_protocol_index = choose_otel_exporter_protocol($otel_exporter_otlp_protocols);
  putenv('OTEL_EXPORTER_OTLP_METRICS_PROTOCOL=' . $otel_exporter_otlp_protocols[$metrics_protocol_index]);
  echo "\n";

  $OTEL_EXPORTER_OTLP_ENDPOINT = "http://localhost:4318";
  $val = readline("set OTEL_EXPORTER_OTLP_ENDPOINT=[http://localhost:4318]: ");
  if ($val == "") {
    $val = "http://localhost:4318";
  }
  $OTEL_EXPORTER_OTLP_ENDPOINT = $val;
  putenv('OTEL_EXPORTER_OTLP_ENDPOINT=' . $OTEL_EXPORTER_OTLP_ENDPOINT);
  echo "\n";

  $OTEL_EXPORTER_OTLP_ENDPOINT = "http://localhost:9411/api/v2/spans";
  $val = readline("set OTEL_EXPORTER_OTLP_ENDPOINT=[http://localhost:9411/api/v2/spans]: ");
  if ($val == "") {
    $val = "http://localhost:9411/api/v2/spans";
  }
  $OTEL_EXPORTER_ZIPKIN_ENDPOINT = $val;
  putenv('OTEL_EXPORTER_ZIPKIN_ENDPOINT=' . $OTEL_EXPORTER_ZIPKIN_ENDPOINT);
  echo "\n";

  $php_traces_procesor_index = choose_otel_php_traces_processor($otel_php_traces_procesors);
  putenv('OTEL_PHP_TRACES_PROCESSOR=' . $otel_php_traces_procesors[$php_traces_procesor_index]);
  echo "\n";

  $OTEL_SERVICE_NAME = 'auto';
  do {
    $OTEL_SERVICE_NAME = readline("set OTEL_SERVICE_NAME: ");
  } while ($OTEL_SERVICE_NAME == 0);
  putenv('OTEL_SERVICE_NAME=' . $OTEL_SERVICE_NAME);
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
  return $provider_index - 1;
}

function choose_version($versions):int {
  return choose_element($versions, count($versions), "version");
}

function make_advanced_setup($packages) {
  $providers = get_php_async_client_impl();
  colorLog("\nChoose http client async provider:\n", 'e');
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
      colorLog("\nChoose version for " . $package . ":\n", 'e');
      exec($cmd, $output, $result_code);
      $versions = get_versions($output, 'i');
      $version_index = choose_version($versions); 
      execute_command(make_composer_require_command(
        $package,
        ":" . $versions[$version_index],
        "--with-all-dependencies"), " 2>&1");
  }
  // C extension is taken and installed from source code
  // this is intermediate step and kind of workaround
  // until extension will be available at PECL
  // For this reason, version from main is installed
  execute_command(make_pickle_install(
    "https://github.com/open-telemetry/opentelemetry-php-instrumentation.git",
    "#main", ""), " 2>&1");
}

$mode = check_args($argc, $argv);

if ($mode == "default") {
  make_default_setup($dependencies, $opentelemetry_packages);
} else {
  make_advanced_setup($opentelemetry_packages);
  set_env($otel_traces_exporters,
          $otel_exporter_otlp_protocols,
          $otel_metrics_exporters,
          $otel_php_traces_procesors);
}
