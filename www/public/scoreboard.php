<?php
/**
 * Produce a total score. Call with parameter 'static' for
 * output suitable for static HTML pages.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');

// set auto refresh
$refresh = "30;url=scoreboard";

require(LIBWWWDIR . '/header.php');
?>

    <div class="container main-container">

        <?php putScoreBoard($cdata, null, true, false); ?>

        <a onclick="history.back()" class="btn hidden-print">back</a>

    </div>

<?php require(LIBWWWDIR . '/footer.php');