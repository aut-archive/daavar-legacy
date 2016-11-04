<?php
/**
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */
require_once('init.php');
$title = 'Daavar - '.htmlspecialchars($teamdata['name']);

$problemid = null;
if (isset($_REQUEST['phid']))
    $problemid = intval($_REQUEST['phid']);

if (isset($_GET['ajax'])) {
    switch ($_GET['ajax']) {
        case 'submissions':
            putSubmissions($cdata, array('teamid' => $teamid), MaxSubmissions, null, true);
            break;
    }
    exit;
}


require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/forms.php');

?>

    <div class="container main-container">
        <div class="row">

            <div class="col-md-3" id="sidebar">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Problems</h3>
                    </div>
                    <div class="panel-body">

                        <ul class="nav nav-pills nav-stacked">

                            <?php if (have_problemtexts() && calcFreezeData($cdata)['cstarted']) {
                                $res = $DB->q('SELECT p.probid,p.shortname,p.name,p.color
		               FROM problem p WHERE cid = %i AND allow_submit = 1 AND
		               problemtext_type IS NOT NULL ORDER BY p.shortname', $cid);
                                if ($res->count() > 0) {
                                    while ($row = $res->next()) {
                                        print '<li class="alist-group-item"> ' .
                                            '<a href="problem?id=' . urlencode($row['probid']) . '">' .
                                            '<i class="icon-chevron-right"></i>  ' .
                                             htmlspecialchars($row['shortname']) . ' (' .
                                            htmlspecialchars($row['name']) . ")</a></li>\n";
                                    }
                                }
                            } else {
                                ?>
                                <li class="alist-group-item">
                                    <div class='nodata' style='text-align: center'><br><i class='icon-bell'></i>Problem
                                        texts
                                        will appear here
                                        <br>at contest start
                                    </div>
                                    <br>
                                </li>
                            <?php } ?>

                        </ul>

                    </div>
                </div>

            </div>

            <div class="col-md-9">

                <h2>Team Status</h2>

                <?php putTeamRow($cdata, array($teamid)); ?>


                <br><br>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Submissions</h3>
                    </div>
                    <div class="panel-body">
                        <?php
                        putSubmissions($cdata, array('teamid' => $teamid), MaxSubmissions, null, true);
                        ?>
                    </div>
                </div>
            </div>


        </div>
    </div>


<?php require(LIBWWWDIR . '/footer.php');

