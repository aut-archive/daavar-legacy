<?php
/**
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require_once('init.php');

$title = htmlspecialchars($teamdata['name']);
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/forms.php');

$id = getRequestID();
if (empty($id)) error("Missing problem id");

$prob = $DB->q("MAYBETUPLE SELECT cid, shortname,name, probid , problemtext, problemtext_type,color,timelimit
	                FROM problem WHERE OCTET_LENGTH(problemtext) > 0
	                AND probid = %i", $id);

$filename = "prob-$prob[shortname].$prob[problemtext_type]";


if (empty($prob) || !(IS_JURY || ($prob['cid'] == $cdata['cid'] && difftime($cdata['starttime'], now()) <= 0)))
    error("Problem $probid not found or not available");

if ($prob['problemtext_type'] != 'txt' && $prob['problemtext_type'] !='html') {
    // download a given problem statement
    putProblemText($id);
    exit;
}
?>
    <div class="container main-container">

            <h1><?php echo $prob['shortname'] . ' : ' . $prob['name']; ?></h1>
            <br>

            <div class="pull-right">
                <i class="glyphicon glyphicon-time"></i>
                Time limit : <?php echo $prob['timelimit'] ?> seconds
            </div>
            <hr>

                <div class="pull-right">
                    <form action="submit" method="GET" style="display: inline;">
                        <input type="hidden" name="id" value="<?php echo $prob['probid'] ?>">
                        <input type="submit" class="btn btn-success" value="Submit answer"/>
                    </form>
                    <form action="submissions" method="GET" style="display: inline;">
                        <input type="hidden" name="id" value="<?php echo $prob['probid'] ?>">
                        <input type="submit" class="btn btn-info" value="View submissions"/>
                    </form>
                </div>
                <br><br>

                <?php echo $prob['problemtext']; ?>

                <br><br>
            </div>
        </div>
    </div>
<?php
require(LIBWWWDIR . '/footer.php');
?>