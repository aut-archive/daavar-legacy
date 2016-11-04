<?php
/**
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

$REQUIRED_ROLES = array('jury', 'balloon');
require('init.php');

header('HTTP/1.1 302 Please see this page');
header('Location: scoreboard');

