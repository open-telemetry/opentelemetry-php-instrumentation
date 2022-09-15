dnl config.m4 for extension otel_instrumentation

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary.

dnl If your extension references something external, use 'with':

dnl PHP_ARG_WITH([otel_instrumentation],
dnl   [for otel_instrumentation support],
dnl   [AS_HELP_STRING([--with-otel_instrumentation],
dnl     [Include otel_instrumentation support])])

dnl Otherwise use 'enable':

PHP_ARG_ENABLE([otel_instrumentation],
  [whether to enable otel_instrumentation support],
  [AS_HELP_STRING([--enable-otel_instrumentation],
    [Enable otel_instrumentation support])],
  [no])

if test "$PHP_OTEL_INSTRUMENTATION" != "no"; then
  dnl Write more examples of tests here...

  dnl Remove this code block if the library does not support pkg-config.
  dnl PKG_CHECK_MODULES([LIBFOO], [foo])
  dnl PHP_EVAL_INCLINE($LIBFOO_CFLAGS)
  dnl PHP_EVAL_LIBLINE($LIBFOO_LIBS, OTEL_INSTRUMENTATION_SHARED_LIBADD)

  dnl If you need to check for a particular library version using PKG_CHECK_MODULES,
  dnl you can use comparison operators. For example:
  dnl PKG_CHECK_MODULES([LIBFOO], [foo >= 1.2.3])
  dnl PKG_CHECK_MODULES([LIBFOO], [foo < 3.4])
  dnl PKG_CHECK_MODULES([LIBFOO], [foo = 1.2.3])

  dnl Remove this code block if the library supports pkg-config.
  dnl --with-otel_instrumentation -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/otel_instrumentation.h"  # you most likely want to change this
  dnl if test -r $PHP_OTEL_INSTRUMENTATION/$SEARCH_FOR; then # path given as parameter
  dnl   OTEL_INSTRUMENTATION_DIR=$PHP_OTEL_INSTRUMENTATION
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for otel_instrumentation files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       OTEL_INSTRUMENTATION_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$OTEL_INSTRUMENTATION_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the otel_instrumentation distribution])
  dnl fi

  dnl Remove this code block if the library supports pkg-config.
  dnl --with-otel_instrumentation -> add include path
  dnl PHP_ADD_INCLUDE($OTEL_INSTRUMENTATION_DIR/include)

  dnl Remove this code block if the library supports pkg-config.
  dnl --with-otel_instrumentation -> check for lib and symbol presence
  dnl LIBNAME=OTEL_INSTRUMENTATION # you may want to change this
  dnl LIBSYMBOL=OTEL_INSTRUMENTATION # you most likely want to change this

  dnl If you need to check for a particular library function (e.g. a conditional
  dnl or version-dependent feature) and you are using pkg-config:
  dnl PHP_CHECK_LIBRARY($LIBNAME, $LIBSYMBOL,
  dnl [
  dnl   AC_DEFINE(HAVE_OTEL_INSTRUMENTATION_FEATURE, 1, [ ])
  dnl ],[
  dnl   AC_MSG_ERROR([FEATURE not supported by your otel_instrumentation library.])
  dnl ], [
  dnl   $LIBFOO_LIBS
  dnl ])

  dnl If you need to check for a particular library function (e.g. a conditional
  dnl or version-dependent feature) and you are not using pkg-config:
  dnl PHP_CHECK_LIBRARY($LIBNAME, $LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $OTEL_INSTRUMENTATION_DIR/$PHP_LIBDIR, OTEL_INSTRUMENTATION_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_OTEL_INSTRUMENTATION_FEATURE, 1, [ ])
  dnl ],[
  dnl   AC_MSG_ERROR([FEATURE not supported by your otel_instrumentation library.])
  dnl ],[
  dnl   -L$OTEL_INSTRUMENTATION_DIR/$PHP_LIBDIR -lm
  dnl ])
  dnl
  dnl PHP_SUBST(OTEL_INSTRUMENTATION_SHARED_LIBADD)

  dnl In case of no dependencies
  AC_DEFINE(HAVE_OTEL_INSTRUMENTATION, 1, [ Have otel_instrumentation support ])

  PHP_NEW_EXTENSION(otel_instrumentation, otel_instrumentation.c otel_observer.c, $ext_shared)
fi
