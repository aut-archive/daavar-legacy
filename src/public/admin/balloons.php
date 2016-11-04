<?php
/**
 * Tool to coordinate the handing out of balloons to teams that solved
 * a problem. Similar to the balloons-daemon, but web-based.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

$REQUIRED_ROLES = array('jury', 'balloon');
require('init.php');
$title = 'Balloon Status';

if (isset($_POST['done'])) {
    foreach ($_POST['done'] as $done => $dummy) {
        $DB->q('UPDATE balloon SET done=1
			WHERE balloonid = %i',
            $done);
        auditlog('balloon', $done, 'marked done');
    }
    header('Location: balloons.php');
}

$viewall = TRUE;

// Restore most recent view from cookie (overridden by explicit selection)
if (isset($_COOKIE['domjudge_balloonviewall'])) {
    $viewall = $_COOKIE['domjudge_balloonviewall'];
}

// Did someone press the view button?
if (isset($_REQUEST['viewall'])) $viewall = $_REQUEST['viewall'];

setcookie('domjudge_balloonviewall', $viewall);

$refresh = '15;url=balloons.php';
require(LIBWWWDIR . '/header.php');
?>

    <div class="block-content collapse in">
        <h1>Balloon Status</h1>
        <br>

        <div>
            <?php
            if (isset($cdata['freezetime']) && difftime($cdata['freezetime'], now()) <= 0) {
                echo "<h4>Scoreboard is now frozen.</h4>\n\n";
            }

            echo addForm($pagename, 'get') . "<p><div class='pull-right'>" .
                addHidden('viewall', ($viewall ? 0 : 1)) .
                addSubmit($viewall ? 'view unsent only' : 'view all', null, null, true, 'class="btn btn-sm btn-info"') . "</div></p>\n" .
                addEndForm().'<br>';

            // Problem metadata: colours and names.
            $probs_data = $DB->q('KEYTABLE SELECT probid AS ARRAYKEY,name,color
		      FROM problem WHERE cid = %i', $cid);

            $freezecond = '';
            if (!dbconfig_get('show_balloons_postfreeze', 0) && isset($cdata['freezetime'])) {
                $freezecond = 'AND submittime <= "' . $cdata['freezetime'] . '"';
            }

            // Get all relevant info from the balloon table.
            // Order by done, so we have the unsent balloons at the top.
            $res = $DB->q("SELECT b.*, s.submittime, p.probid, p.shortname AS probshortname,
               t.teamid, t.name AS teamname, t.room, c.name AS catname
               FROM balloon b
               LEFT JOIN submission s USING (submitid)
               LEFT JOIN problem p USING (probid)
               LEFT JOIN team t USING(teamid)
               LEFT JOIN team_category c USING(categoryid)
               WHERE s.cid = %i $freezecond
               ORDER BY done ASC, balloonid DESC",
                $cid);

            /* Loop over the result, store the total of balloons for a team
             * (saves a query within the inner loop).
             * We need to store the rows aswell because we can only next()
             * once over the db result.
             */
            $BALLOONS = $TOTAL_BALLOONS = array();
            while ($row = $res->next()) {
                $BALLOONS[] = $row;
                $TOTAL_BALLOONS[$row['teamid']][] = $row['probid'];

                // keep overwriting these variables - in the end they'll
                // contain the id's of the first balloon in each type
                $first_contest = $first_problem[$row['probid']] = $first_team[$row['teamid']] = $row['balloonid'];
            }

            if (!empty($BALLOONS)) {
                echo addForm($pagename);

                echo "<br><table class=\"table list sortable balloons\">\n<thead>\n" .
                    "<tr><th class=\"sorttable_numeric\">ID</th>" .
                    "<th>Time</th><th>Solved</th><th>Team</th>" .
                    "<th></th><th>Loc</th><th>Category</th><th>Total</th>" .
                    "<th></th><th></th></tr>\n</thead>\n";

                foreach ($BALLOONS as $row) {

                    if (!$viewall && $row['done'] == 1) continue;

                    // start a new row, 'disable' if balloon has been handed out already
                    echo '<tr' . ($row['done'] == 1 ? ' class="disabled"' : '') . '>';
                    echo '<td>b' . (int)$row['balloonid'] . '</td>';
                    echo '<td>' . printtime($row['submittime']) . '</td>';

                    // the balloon earned
                    echo '<td class="probid">' .
                        '<div class="circle" style="background-color: ' .
                        htmlspecialchars($probs_data[$row['probid']]['color']) .
                        ';"></div> ' . htmlspecialchars($row['probshortname']) . '</td>';

                    // team name, location (room) and category
                    echo '<td>t' . htmlspecialchars($row['teamid']) . '</td><td>' .
                        htmlspecialchars($row['teamname']) . '</td><td>' .
                        htmlspecialchars($row['room']) . '</td><td>' .
                        htmlspecialchars($row['catname']) . '</td><td>';

                    // list of balloons for this team
                    sort($TOTAL_BALLOONS[$row['teamid']]);
                    $TOTAL_BALLOONS[$row['teamid']] = array_unique($TOTAL_BALLOONS[$row['teamid']]);
                    foreach ($TOTAL_BALLOONS[$row['teamid']] as $prob_solved) {
                        echo '<div title="' . htmlspecialchars($prob_solved) .
                            '" class="circle" style="background-color: ' .
                            htmlspecialchars($probs_data[$prob_solved]['color']) .
                            ';"></div> ';
                    }
                    echo '</td><td>';

                    // 'done' button when balloon has yet to be handed out
                    if ($row['done'] == 0) {
                        echo '<br><input class="btn btn-sm btn-success" type="submit" name="done[' .
                            (int)$row['balloonid'] . ']" value="Done" />';
                    }

                    echo '</td><td>';

                    $comments = array();
                    if ($first_contest == $row['balloonid']) {
                        $comments[] = 'first in contest';
                    } else {
                        if ($first_team[$row['teamid']] == $row['balloonid']) {
                            $comments[] = '<span class="label label-default">1<sup>st</sup> For Team</span>';
                        }
                        if ($first_problem[$row['probid']] == $row['balloonid']) {
                            $comments[] = '<span class="label label-info">1<sup>st</sup> For Problem</span>';
                        }
                    }
                    echo implode('; ', $comments);

                    echo "</td></tr>\n";
                }

                echo "</table>\n\n" . addEndForm();
            } else {
                echo "<p class=\"nodata\">No correct submissions yet... keep posted!</p>\n\n";
            }
            ?>
        </div>
    </div>
<?php require(LIBWWWDIR . '/footer.php');
