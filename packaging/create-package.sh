#!/usr/bin/env sh
set -x

## so files manipulation to avoid specific platform so files
##   - If alpine then use only alpine so files
##   - If deb/rpm then skip alpine so files
##   - If tar then use all the so files

BUILD_EXT_DIR=""

if [ "${TYPE}" = 'apk' ] ; then
	BUILD_EXT_DIR=instrumentation/native/_build/linuxmusl-x86-64-release/ext/
else
	BUILD_EXT_DIR=instrumentation/native/_build/linux-x86-64-release/ext/
fi

touch build/open-telemetry.ini

function createPackage () {

mkdir -p /tmp/extensions
cp ${BUILD_EXT_DIR}/*.so /tmp/extensions/

fpm --input-type dir \
		--output-type "${TYPE}" \
		--name "${NAME}" \
		--version "${VERSION}" \
		--architecture all \
		--url 'https://github.com/open-telemetry/opentelemetry-php-instrumentation' \
		--license 'ASL 2.0' \
		--vendor 'OpenTelemetry' \
		--description "OpenTelemetry auto-instrumentation PHP extension\nGit Commit: ${GIT_SHA}" \
		--package "${OUTPUT}" \
		${FPM_FLAGS} \
		--after-install=packaging/post-install.sh \
		--before-remove=packaging/before-uninstall.sh \
		--directories ${PHP_INSTRUMENTATION_DIR}/etc \
		--config-files ${PHP_INSTRUMENTATION_DIR}/etc \
		/app/packaging/post-install.sh=${PHP_INSTRUMENTATION_DIR}/bin/post-install.sh \
		/app/build/open-telemtry.ini=${PHP_INSTRUMENTATION_DIR}/etc/ \
		/app/packaging/before-uninstall.sh=${PHP_INSTRUMENTATION_DIR}/bin/before-uninstall.sh \
		/app/instrumentation/php/=${PHP_INSTRUMENTATION_DIR}/src \
		/tmp/extensions/=${PHP_INSTRUMENTATION_DIR}/extensions \
		/app/README.md=${PHP_INSTRUMENTATION_DIR}/docs/README.md

rm -rf /tmp/extensions

## Create sha512
BINARY=$(ls -1 "${OUTPUT}"/${NAME}*."${TYPE}")
SHA=${BINARY}.sha512
sha512sum "${BINARY}" > "${SHA}"
sed -i.bck "s#${OUTPUT}/##g" "${SHA}"
rm "${OUTPUT}"/*.bck

}


function createDebugPackage () {

mkdir -p /tmp/extensions
cp ${BUILD_EXT_DIR}/*.debug /tmp/extensions/

fpm --input-type dir \
		--output-type "${TYPE}" \
		--name "${NAME}" \
		--version "${VERSION}" \
		--architecture all \
		--url 'https://github.com/open-telemetry/opentelemetry-php-instrumentation' \
		--license 'ASL 2.0' \
		--vendor 'OpenTelemetry' \
		--description "OpenTelemetry auto-instrumentation PHP extension debug symbols\nGit Commit: ${GIT_SHA}" \
		--package "${OUTPUT}" \
		${FPM_FLAGS} \
		/tmp/extensions/=${PHP_INSTRUMENTATION_DIR}/extensions

rm -rf /tmp/extensions

## Create sha512
BINARY=$(ls -1 "${OUTPUT}"/${NAME}*."${TYPE}")
SHA=${BINARY}.sha512
sha512sum "${BINARY}" > "${SHA}"
sed -i.bck "s#${OUTPUT}/##g" "${SHA}"
rm "${OUTPUT}"/*.bck

}




# create second tar for musl
if [ "${TYPE}" = 'tar' ] ; then
	NAME_BACKUP=${NAME}
	NAME="${NAME_BACKUP}-linux-x86-64"
	BUILD_EXT_DIR=instrumentation/native/_build/linux-x86-64-release/ext/
	createPackage

	NAME="${NAME_BACKUP}-debugsymbols-linux-x86-64"
	createDebugPackage

	NAME="${NAME_BACKUP}-linuxmusl-x86-64"
	BUILD_EXT_DIR=instrumentation/native/_build/linuxmusl-x86-64-release/ext/
	createPackage

	NAME="${NAME_BACKUP}-debugsymbols-linuxmusl-x86-64"
	createDebugPackage
else
	createPackage
fi