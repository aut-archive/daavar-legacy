<?php
/**
 * This page calls the logout function.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require_once('init.php');


logged_in(); // To fill information if the user is logged in.
do_logout();
