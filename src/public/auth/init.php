<?php

define('IS_JURY', false);
define('IS_PUBLIC', true);

require_once('../configure.php');
require_once(LIBDIR . '/init.php');
require_once(LIBWWWDIR . '/common.php');;
require_once(LIBWWWDIR . '/auth.php');
setup_database_connection();

