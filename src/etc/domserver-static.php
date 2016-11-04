<?php

define('BASEDIR',     realpath(dirname(__DIR__)) );
define('WORKDIR',     BASEDIR.'/workspace');

define('BINDIR',      BASEDIR.'/bin');
define('ETCDIR',      BASEDIR.'/etc');
define('WWWDIR',      BASEDIR.'/public');
define('LIBDIR',      BASEDIR.'/lib');
define('LIBEXTDIR',   BASEDIR.'/lib/ext');
define('LIBWWWDIR',   BASEDIR.'/lib/www');
define('LIBSUBMITDIR',BASEDIR.'/lib/submit');

define('LOGDIR',      WORKDIR.'/log');
define('RUNDIR',      WORKDIR.'/run');
define('TMPDIR',      WORKDIR.'/tmp');
define('SUBMITDIR',   WORKDIR.'/submissions');

set_include_path(get_include_path() . PATH_SEPARATOR . LIBEXTDIR);

/** Loglevels and debugging */

// Log to syslog facility; do not define to disable.
define('SYSLOG', LOG_LOCAL0);

// Set DEBUG as a bitmask of the following settings.
// Of course never to be used on live systems!

define('DEBUG_PHP_NOTICE', 1); // Display PHP notice level warnings
define('DEBUG_TIMINGS',    2); // Display timings for loading webpages
define('DEBUG_SQL',        4); // Display SQL queries on webpages
define('DEBUG_JUDGE',      8); // Display judging scripts debug info

