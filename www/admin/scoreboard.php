<?php

/**
 * Scoreboard
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

$REQUIRED_ROLES = array('balloon');

require('init.php');

$refresh = '30;url=scoreboard.php';
$title = 'Scoreboard';
$printercss = TRUE;


require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/scoreboard.php');

if (checkrole('JURY')) {
    // parse filter options
    $filter = array();
    if (!isset($_GET['clear'])) {
        foreach (array('affilid', 'country', 'categoryid') as $type) {
            if (!empty($_GET[$type])) $filter[$type] = $_GET[$type];
        }
        if (count($filter)) $refresh .= '?' . http_build_query($filter);
    }
    // call the general putScoreBoard function from scoreboard.php
    putScoreBoard($cdata, NULL, FALSE, $filter);
} else {
    putScoreBoard($cdata, null, true, false);
}

require(LIBWWWDIR . '/footer.php');
