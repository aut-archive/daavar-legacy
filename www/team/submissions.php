<?php
/**
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');

$restrictions = array();

global $pid;
$pid = getRequestID();
if (!empty($pid))
    $restrictions['probid'] = $pid;

$showall = isset($_REQUEST['all']);

if (!$showall || !ShowOtherTeamSubmissions)
    $restrictions['teamid'] = $teamid;

if (isset($_GET['ajax'])) {
    switch ($_GET['ajax']) {
        case 'submissions':
            putSubmissions($cdata, $restrictions, MaxSubmissions, null, true);
            break;
    }
    exit;
}


$title = htmlspecialchars($teamdata['name']);
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/forms.php');


if (!empty($pid)) {
    $prob = $DB->q("MAYBETUPLE SELECT cid, shortname,name, probid , problemtext, problemtext_type,color,timelimit
	                FROM problem WHERE OCTET_LENGTH(problemtext) > 0
	                AND probid = %i", $pid);
    $filename = "prob-$prob[shortname].$prob[problemtext_type]";
    if (empty($prob) || !(IS_JURY || ($prob['cid'] == $cdata['cid'] && difftime($cdata['starttime'], now()) <= 0)))
        error("Problem $probid not found or not available");
}

?>


<div class="container main-container">

    <h1>Submissions <?php if (!empty($pid)) echo 'for Problem ' . $prob['shortname'] . ' (' . $prob['name'].')' ?></h1>

    <div class="pull-right">
        <?php if (!$showall && ShowOtherTeamSubmissions): ?>
            <a href="submissions?all<?php if (!empty($pid)) echo "&id=$pid"; ?>">Show all teams</a>
        <?php elseif ($showall && ShowOtherTeamSubmissions) : ?>
            <a href="submissions?<?php if (!empty($pid)) echo "&id=$pid"; ?>">Show only my team</a>
        <?php endif ?>
    </div>

    <?php if (!empty($pid)): ?>
        <div class="pull-left">
            <form action="submit" method="GET" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $prob['probid'] ?>">
                <input type="submit" class="btn btn-success btn-mini" value="Submit answer"/>
            </form>
            <form action="problem" method="GET" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $prob['probid'] ?>">
                <input type="submit" class="btn btn-info btn-mini" value="View problem"/>
            </form>
        </div>
        <br><br>
    <?php endif ?>
    <?php putSubmissions($cdata, $restrictions, MaxSubmissions, null, true); ?>
    <br>


</div>


<?php
require(LIBWWWDIR . '/footer.php');
?>
