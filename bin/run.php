<?php

// This simple CLI script runs sets needed env
// variables before running php application
// everything what's needed for auto-instrumentation.

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


$otel_php_traces_procesors = array(
  "batch",
  "simple",
  "noop",
  "none"
);

function choose_element($elements, $default_index, $command_line):int {
  $message = "Choose " . $command_line . " (1-" . count($elements) . ") [" . $default_index . "] : ";
  $colorMessage = "\033[31m$message \033[0m";
  $counter = 1;
  foreach ($elements as $element) {
    echo($counter . ") " . $element . "\n");
    ++$counter;
  }
  echo "\n";
  $element_index = count($elements) - 1;
  do {
    $element_index = intval(readline($colorMessage));
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
                   $otel_php_traces_procesors) {
    $OTEL_PHP_AUTOLOAD_ENABLED = true;
    $message = "set OTEL_PHP_AUTOLOAD_ENABLED=[true]: ";
    $colorMessage = "\033[31m$message \033[0m";
    $val = "";
    do {
      $val = readline($colorMessage);
      if ($val == "") {
        $val = "true";
        break;
      }
    } while ($val != "true" && $val != "false");
    putenv('OTEL_PHP_AUTOLOAD_ENABLED=' . $val);
    echo "\n";    
  
    $exporter_index = choose_otel_traces_exporter($otel_traces_exporters);
    putenv('OTEL_TRACES_EXPORTER=' . $otel_traces_exporters[$exporter_index]);
    echo "\n";
  
    // $protocol_index = choose_otel_exporter_protocol($otel_exporter_otlp_protocols);
    // putenv('OTEL_EXPORTER_OTLP_PROTOCOL=' . $otel_exporter_otlp_protocols[$protocol_index]);
    // echo "\n";
  
    $OTEL_EXPORTER_OTLP_ENDPOINT = "http://localhost:4318";
    $message = "set OTEL_EXPORTER_OTLP_ENDPOINT=[http://localhost:4318]: ";
    $colorMessage = "\033[31m$message \033[0m";

    $val = readline($colorMessage);
    if ($val == "") {
      $val = "http://localhost:4318";
    }
    $OTEL_EXPORTER_OTLP_ENDPOINT = $val;
    putenv('OTEL_EXPORTER_OTLP_ENDPOINT=' . $OTEL_EXPORTER_OTLP_ENDPOINT);
    echo "\n";
  
    $message = "set OTEL_EXPORTER_ZIPKIN_ENDPOINT=[http://localhost:9411/api/v2/spans]: ";
    $colorMessage = "\033[31m$message \033[0m";
    $OTEL_EXPORTER_ZIPKIN_ENDPOINT = "http://localhost:9411/api/v2/spans";
    $val = readline($colorMessage);
    if ($val == "") {
      $val = "http://localhost:9411/api/v2/spans";
    }
    $OTEL_EXPORTER_ZIPKIN_ENDPOINT = $val;
    putenv('OTEL_EXPORTER_ZIPKIN_ENDPOINT=' . $OTEL_EXPORTER_ZIPKIN_ENDPOINT);
    echo "\n";
  
    $php_traces_procesor_index = choose_otel_php_traces_processor($otel_php_traces_procesors);
    putenv('OTEL_PHP_TRACES_PROCESSOR=' . $otel_php_traces_procesors[$php_traces_procesor_index]);
    echo "\n";
  
    $OTEL_SERVICE_NAME = "auto";
    $message = "set OTEL_SERVICE_NAME: ";
    $colorMessage = "\033[31m$message \033[0m";

    $val = readline($colorMessage);
    if ($val == "") {
      $val = "auto";
    } 
    $OTEL_SERVICE_NAME = $val;
    putenv('OTEL_SERVICE_NAME=' . $OTEL_SERVICE_NAME);
  }

  set_env($otel_traces_exporters,
  $otel_exporter_otlp_protocols,
  $otel_php_traces_procesors);

  $command = "";
  for ($i = 1; $i < $argc; $i++) {
    if ($i > 1) {
        $command = $command . " ";
    }
    $command = $command . $argv[$i];  
  }

  if ($command != "") {
    echo $command . "\n";
    exec($command);
  }
