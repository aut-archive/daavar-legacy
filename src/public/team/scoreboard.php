<?php
/**
 * Scoreboard
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */
require_once('init.php');

$pagename = basename($_SERVER['PHP_SELF']);
$refresh = '30;url=scoreboard.php';
$title = 'Scoreboard';

// parse filter options
$filter = array();
if (!isset($_GET['clear'])) {
    foreach (array('affilid', 'country', 'categoryid') as $type) {
        if (!empty($_GET[$type])) $filter[$type] = $_GET[$type];
    }
    if (count($filter)) $refresh .= '?' . http_build_query($filter);
}

require(LIBWWWDIR . '/header.php');

// call the general putScoreBoard function from scoreboad.php
?>


<div class="container main-container">
    <?php putScoreBoard($cdata, $teamid, true, $filter); ?>
</div>
<?php require(LIBWWWDIR . '/footer.php');?>
