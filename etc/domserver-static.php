<?php

define('BASEDIR',     realpath(dirname(__DIR__)));
define('BINDIR',      BASEDIR.'/bin');
define('ETCDIR',      BASEDIR.'/etc');
define('WWWDIR',      BASEDIR.'/www');
define('LIBDIR',      BASEDIR.'/lib');
define('LIBEXTDIR',   BASEDIR.'/lib/ext');
define('LIBWWWDIR',   BASEDIR.'/lib/www');
define('LIBSUBMITDIR',BASEDIR.'/lib/submit');
define('LOGDIR',      BASEDIR.'/log');
define('RUNDIR',      BASEDIR.'/run');
define('TMPDIR',      BASEDIR.'/tmp');
define('SUBMITDIR',   BASEDIR.'/submissions');

define('SUBMITCLIENT_ENABLED', 'yes');

set_include_path(get_include_path() . PATH_SEPARATOR . LIBEXTDIR);
