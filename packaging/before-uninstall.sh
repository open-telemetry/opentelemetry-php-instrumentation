#!/usr/bin/env bash

######### Let's support alpine installations
PATH=${PATH}:/usr/local/bin

################################################################################
############################ GLOBAL VARIABLES ##################################
################################################################################
####### IMPORTANT: PHP_INSTRUMENTATION_DIR is needed otherwise fpm will fail when generating
#######            the apk distribution with an Invalid tar stream error.
PHP_INSTRUMENTATION_DIR=/opt/open-telemetry/php-instrumentation
BACKUP_EXTENSION=".instrumentation.uninstall.bck"

################################################################################
########################## FUNCTION CALLS BELOW ################################
################################################################################

################################################################################
#### Function php_command ######################################################
function php_command() {
    PHP_BIN=$(command -v php)
    ${PHP_BIN} -d memory_limit=128M "$@"
}

################################################################################
#### Function php_ini_file_path ################################################
function php_ini_file_path() {
    php_command -i \
        | grep 'Configuration File (php.ini) Path =>' \
        | sed -e 's#Configuration File (php.ini) Path =>##g' \
        | head -n 1 \
        | awk '{print $1}'
}

################################################################################
#### Function php_api ##########################################################
function php_api() {
    php_command -i \
        | grep 'PHP API' \
        | sed -e 's#.* =>##g' \
        | awk '{print $1}'
}

################################################################################
#### Function php_config_d_path ################################################
function php_config_d_path() {
    php_command -i \
        | grep 'Scan this dir for additional .ini files =>' \
        | sed -e 's#Scan this dir for additional .ini files =>##g' \
        | head -n 1 \
        | awk '{print $1}'
}

################################################################################
#### Function is_extension_enabled #############################################
function is_extension_enabled() {
    php_command -m | grep -q 'opentelemetry'
}

################################################################################
#### Function get_extension_filename ###############################################
function get_extension_filename() {
    PHP_API=$(php_api)
    ## If alpine then add another suffix
#     if grep -q -i alpine /etc/os-release; then
#        SUFFIX=-alpine
#     fi
    echo "opentelemetry-${PHP_API}${SUFFIX}.so"
}

################################################################################
#### Function manual_extension_instrumentation_uninstallation ############################
function manual_extension_instrumentation_uninstallation() {
    echo 'Uninstall the instrumentation manually as explained in:'
    echo 'https://github.com/open-telemetry/opentelemetry-php-instrumentation/blob/main/docs/setup.md'
}

################################################################################
#### Function uninstall_conf_d_files ###########=###############################
function uninstall_conf_d_files() {
    PHP_CONFIG_D_PATH=$1

    echo "Uninstalling ${OPENTELEMETRY_INI_FILE_NAME} for supported SAPI's"

    # Detect installed SAPI's
    SAPI_DIR=${PHP_CONFIG_D_PATH%/*/conf.d}/
    SAPI_CONFIG_DIRS=()
    if [ "${PHP_CONFIG_D_PATH}" != "${SAPI_DIR}" ]; then
        # CLI
        CLI_CONF_D_PATH="${SAPI_DIR}cli/conf.d"
        if [ -d "${CLI_CONF_D_PATH}" ]; then
            SAPI_CONFIG_DIRS+=("${CLI_CONF_D_PATH}")
        fi
        # Apache
        APACHE_CONF_D_PATH="${SAPI_DIR}apache2/conf.d"
        if [ -d "${APACHE_CONF_D_PATH}" ]; then
            SAPI_CONFIG_DIRS+=("${APACHE_CONF_D_PATH}")
        fi
        ## FPM
        FPM_CONF_D_PATH="${SAPI_DIR}fpm/conf.d"
        if [ -d "${FPM_CONF_D_PATH}" ]; then
            SAPI_CONFIG_DIRS+=("${FPM_CONF_D_PATH}")
        fi
    fi

    if [ ${#SAPI_CONFIG_DIRS[@]} -eq 0 ]; then
        SAPI_CONFIG_DIRS+=("$PHP_CONFIG_D_PATH")
    fi

    for SAPI_CONFIG_D_PATH in "${SAPI_CONFIG_DIRS[@]}" ; do
        echo "Found SAPI config directory: ${SAPI_CONFIG_D_PATH}"
        unlink_file "${SAPI_CONFIG_D_PATH}/98-${OPENTELEMETRY_INI_FILE_NAME}"
        unlink_file "${SAPI_CONFIG_D_PATH}/99-${CUSTOM_INI_FILE_NAME}"
    done
}

################################################################################
#### Function unlink_file ######################################################
function unlink_file() {
    echo "Removing ${1}"
    test -L "${1}" && unlink "${1}"
}

################################################################################
############################### MAIN ###########################################
################################################################################
echo 'Uninstalling OpenTelemetry PHP auto-instrumentation extension'
EXTENSION_FILENAME=$(get_extension_filename)
PHP_INI_FILE_PATH="$(php_ini_file_path)/php.ini"
PHP_CONFIG_D_PATH="$(php_config_d_path)"

echo "DEBUG: before-remove parameter is '$1'"
if [ "$1" = "1" ]; then
    echo "The action is an upgrade in RPM, therefore this is not required"
    exit 0
fi

if [ -e "${PHP_CONFIG_D_PATH}" ]; then
    uninstall_conf_d_files "${PHP_CONFIG_D_PATH}"
fi
if [ -e "${PHP_INI_FILE_PATH}" ] ; then
    if grep -q "${EXTENSION_FILENAME}" "${PHP_INI_FILE_PATH}" ; then
        remove_extension_configuration_to_file "${PHP_INI_FILE_PATH}"
    else
        echo '  extension configuration does not exist for the OpenTelemetry PHP auto-instrumentation.'
        echo '  skipping ... '
    fi
else
    echo 'No default php.ini file has been found.'
fi

if is_extension_enabled ; then
    echo 'Failed. OpenTelemetry PHP auto-instrumentation extension is still enabled.'
    if [ -e "${PHP_INI_FILE_PATH}${BACKUP_EXTENSION}" ] ; then
        echo "Reverted changes in the file ${PHP_INI_FILE_PATH}"
        mv -f "${PHP_INI_FILE_PATH}${BACKUP_EXTENSION}" "${PHP_INI_FILE_PATH}"
        echo "${PHP_INI_FILE_PATH} got some leftovers please delete the entries for the OpenTelemetry PHP auto-instrumentation manually"
    fi
else
    echo 'OpenTelemetry PHP auto-instrumentation extension has been removed successfully.'
fi
