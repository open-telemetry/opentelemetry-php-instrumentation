<?php

// This simple CLI script installs and setup
// everything what's needed for auto-instrumentation.

// default http async implementation
// in basic mode
$dependencies = array(
  "php-http/guzzle7-adapter"
);

// core packages are always installed
// it's possible to get them via packagist API
// however there is no way at the moment
// to distinguish core packages from others
$opentelemetry_core_packages = array(
  "open-telemetry/sdk", 
  "open-telemetry/api",
  "open-telemetry/sdk-contrib",
);

// TODO, this list should be taken through packagist API
$opentelemetry_auto_packages = array(
  "open-telemetry/opentelemetry-auto-slim",
  "open-telemetry/opentelemetry-auto-psr15",
  "open-telemetry/opentelemetry-auto-psr18",
  "open-telemetry/opentelemetry-auto-wordpress",
  "open-telemetry/opentelemetry-auto-pdo",
  "open-telemetry/opentelemetry-auto-symfony",
  "open-telemetry/opentelemetry-auto-laravel",
);

function command_exists($command_name) {
  $os_cmd = 'command -v';
  if (PHP_OS_FAMILY === "Windows") {
    $os_cmd = 'where';
  }
  return (null === shell_exec("$os_cmd $command_name")) ? false : true;
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

function usage() {
  colorLog ("install.php is a script that will help you", 'e');
  colorLog ("install and setup auto-instrumentation for your project.", 'e');
  colorLog ("It works in two modes basic and advanced.", 'e');
  colorLog ("In basic mode it will install everything using some defaults and latest", 'e');
  colorLog ("development versions. Advanced will ask you to choose needed packages and versions.\n", 'e');

  colorLog ("usage : php install.php [basic | advanced]\n", 'e');
}

function check_args($argc, $argv):string {
  if ($argc != 2) {
    usage();
    exit(-1);
  }
  if ($argv[1] != "basic" && $argv[1] != "advanced") {
    usage();
    exit(-1);
  }
  return $argv[1];
}

function get_php_async_client_impl(): array {
  $http_async_client_providers = "https://packagist.org/providers/php-http/async-client-implementation.json";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $http_async_client_providers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  $output = curl_exec($ch);
  curl_close($ch);  
  $json = json_decode($output);
  return $json->{"providers"};
}

function get_pickle() {
  $content = file_get_contents("https://github.com/FriendsOfPHP/pickle/releases/latest/download/pickle.phar");
  $fp = fopen('pickle.phar', 'w');
  fwrite($fp, $content);
  fclose($fp);
}

function check_php_version() {
  if (version_compare(phpversion(), '8.0.0', '<')) {
    colorLog("PHP 8 or higher is required", 'e');
    exit(-1);
  }
}

function check_composer_json():bool {
  return file_exists("composer.json");
}

// There are few preconditions
// - installed php engine 8 or above
// - installed composer
// - c compiler
// - php sdk
// pickle will be installed automatically
function check_preconditions() {
  check_php_version();
  if (!command_exists("composer")) {
    colorLog("composer is not installed", 'e');
    exit(-1);
  }
  if (PHP_OS_FAMILY === "Windows") {
    colorLog("windows is not supported yet", 'e');
    exit(-1);
    if (!command_exists("cl") && !command_exists("link")) {
      colorLog("c compiler is not installed or not available", 'e');
      exit(-1);
    }
  } else {
    if (!command_exists("gcc --version") || !command_exists("clang --version")) {
      colorLog("c compiler is not installed or not available", 'e');
      exit(-1);
    }
  }
  if (!command_exists("phpize")) {
    colorLog("php-sdk is not installed", 'e');
    exit(-1);
  }
  if (!check_composer_json()) {
    colorLog("project does not contain composer.json", 'e');
    exit(-1);
  }
  get_pickle();
}

function is_otel_module_exists():bool {
  $output = array();
  $result_code = null;
  $cmd = "php -m";
  exec($cmd, $output, $result_code);
  if(!in_array("otel_instrumentation", $output)) {
    return false;
  }
  return true;
}

function get_php_info_output():array
{
  $output = array();
  $result_code = null;
  $cmd = "php -i";
  $ini_file_path = "";
  exec($cmd, $output, $result_code);
  return $output;
}

function update_ini_file(array $output) {
  foreach($output as $entry) {
    if (!str_starts_with($entry, "Loaded Configuration File => ")) {
      continue;
    }
    $ini_file_path = substr($entry, strlen("Loaded Configuration File => "));
    break;
  }

  $extension_in_ini = "";
  if (PHP_OS_FAMILY === "Windows") {
    $extension_in_ini = "extension=php_otel_instrumentation.dll";
  } else {
    $extension_in_ini = "extension=otel_instrumentation.so";
  }
  $extension_exists = false;
  if ($ini_file = fopen($ini_file_path, "r")) {
    while(!feof($ini_file)) {
        $entry = fgets($ini_file);
        if (str_starts_with($entry, $extension_in_ini)) {
          $extension_exists = true;
        }
    }
    fclose($ini_file);
  }
  if (!$extension_exists) {
    file_put_contents($ini_file_path, $extension_in_ini, FILE_APPEND);
  }
}

// PHP otel extension is installed
// and added to ini file
function check_postconditions(array $output):bool {
{
    $extension_file = "";
    if (PHP_OS_FAMILY === "Windows") {
      $extension_file = "\php_otel_instrumentation.dll";
    } else {
      $extension_file = "/otel_instrumentation.so";
    }
    if (count($output) == 0) {
      return false;
    }
    foreach($output as $entry) {
      if (!str_starts_with($entry, "extension_dir => ")) {
        continue;
      }
      $extension_dir_entry = substr($entry, strlen("extension_dir => "));
      $extension_dirs = explode(" ", $extension_dir_entry);
      // TODO does not work with relative paths
      if (!file_exists($extension_dirs[0] . $extension_file)) {
        colorLog("\nERROR : otel_instrumentation has not been installed correcly", 'e');
        return false;
      }
    }
  }
  if (!is_otel_module_exists()) {
    colorLog("\nERROR : otel_instrumentation extension has not been added to ini file", 'e');
    false;
  }
  return true;
}

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
  return "php pickle.phar install --source " . $repo . $version . " " . $options;
}

function make_composer_config_command($param, $options) {
  return "composer config " . $param . " " . $options;
}

function make_composer_show_command($package_name) {
  return "composer show -a " . $package_name . " -n";
}

function make_basic_setup($dependencies, $core_packages, $auto_packages) {
  execute_command(make_pickle_install(
    "https://github.com/open-telemetry/opentelemetry-php-instrumentation.git",
    "#main", " -n"), " 2>&1");
  update_ini_file(get_php_info_output());

  execute_command(make_composer_config_command(
    "minimum-stability dev",
    ""), " 2>&1");

  execute_command(make_composer_config_command(
    "--no-plugins allow-plugins.php-http/discovery false",
    ""), " 2>&1");

    foreach ($dependencies as $dep) {
      execute_command(make_composer_require_command(
        $dep, 
        "", 
        "--with-all-dependencies -n"), " 2>&1");
    }
    foreach ($core_packages as $package) {
      execute_command(make_composer_require_command(
        $package,
        " ^1.0 -n",
        ""), " 2>&1");
    }
    foreach ($auto_packages as $package) {
      execute_command(make_composer_require_command(
        $package,
        " -n",
        ""), " 2>&1");
    }
  }

function choose_http_async_impl_provider($providers):int {
  $message = "Choose provider (1-" . count($providers) . "): ";
  $colorMessage = "\033[31m$message \033[0m";
  $counter = 1;
  foreach ($providers as $provider) {
    echo($counter . ") " . $provider->name . "\n");
    ++$counter;
  }
  echo "\n";
  $provider_index = 0;
  do {
    $provider_index = intval(readline($colorMessage));
  } while ($provider_index == 0 && $provider_index < 1 || $provider_index > count($providers)) ;
  return $provider_index - 1;
}

function choose_version($versions, $package):int {
  return choose_element($versions, 1, "version for " . $package);
}

function ask_for($message):bool {
  $val = "";
  $colorMessage = "\033[31m$message \033[0m";
  do {
    $val = readline($colorMessage);
    if ($val == "") {
      $val = "Yes";
      break;
    }
  } while ($val != "Yes" && $val != "No" && $val != "Y" && $val != "N");
  if ($val == "Yes" || $val == "Y") {
    return true;
  }
  return false;
}

function install_package($package):bool {
  $message = "Do you want install " . $package . " [Y]es/No: ";
  return ask_for($message);
}

function make_advanced_setup($core_packages, $auto_packages) {
  // C extension is taken and installed from source code (github)
  // this is intermediate step and kind of workaround
  // until extension will be available at PECL
  // (which for Unix like systems also means from source but via PECL not github)
  // For this reason, version from main is always installed
  execute_command(make_pickle_install(
    "https://github.com/open-telemetry/opentelemetry-php-instrumentation.git",
    "#main", " -n"), " 2>&1");
  $message = "Do you want add extension to php.ini file [Y]es/No: ";
  if (ask_for($message)) {
    update_ini_file(get_php_info_output());
  }

  $providers = get_php_async_client_impl();
  colorLog("\nChoose http client async provider:\n", 'e');
  $provider_index = choose_http_async_impl_provider($providers);
  execute_command(make_composer_config_command(
    "minimum-stability dev",
    ""), " 2>&1");

  execute_command(make_composer_config_command(
    "--no-plugins allow-plugins.php-http/discovery false",
    ""), " 2>&1");

  execute_command(make_composer_require_command(
    $providers[$provider_index]->name,
    "",
    "--with-all-dependencies -n"), " 2>&1");
    foreach ($core_packages as $package) {
      $output = array();
      $result_code = null;
      $cmd = make_composer_show_command($package);
      colorLog("\nChoose version for " . $package . ":\n", 'e');
      exec($cmd, $output, $result_code);
      $versions = get_versions($output, 'i');
      $version_index = choose_version($versions, $package);
      execute_command(make_composer_require_command(
        $package,
        ":" . $versions[$version_index],
        "--with-all-dependencies -n"), " 2>&1");
  }
    foreach ($auto_packages as $package) {
      if(!install_package($package)) {
        continue;
      }
      $output = array();
      $result_code = null;
      $cmd = make_composer_show_command($package);
      colorLog("\nChoose version for " . $package . ":\n", 'e');
      exec($cmd, $output, $result_code);
      $versions = get_versions($output, 'i');
      $version_index = choose_version($versions, $package);
      execute_command(make_composer_require_command(
        $package,
        ":" . $versions[$version_index],
        "--with-all-dependencies -n"), " 2>&1");
  }

}

if (!isset($argc)) {
  return;
}

check_preconditions();
$mode = check_args($argc, $argv);

if ($mode == "basic") {
  make_basic_setup($dependencies, $opentelemetry_core_packages, $opentelemetry_auto_packages);
} else {
  make_advanced_setup($opentelemetry_core_packages, $opentelemetry_auto_packages);
}
unlink("pickle.phar");
if (check_postconditions(get_php_info_output())) {
  colorLog("\notel_instrumentation extension has been successfully installed", 's');
}
