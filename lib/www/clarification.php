<?php
/**
 * Clarification helper functions for jury and teams
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require_once(LIBDIR . '/lib.misc.php');

/**
 * Marks a given clarification as viewed by a specific team,
 * so it doesn't show up as "unread" anymore in their interface.
 */
function setClarificationViewed($clar, $team)
{
    global $DB;
    $DB->q('DELETE FROM team_unread
	        WHERE mesgid = %i AND teamid = %i',
        $clar, $team);
}

/**
 * Returns wether a team is allowed to view a clarification.
 */
function canViewClarification($team, $clar)
{
    return (
        $clar['sender'] == $team
        || $clar['recipient'] == $team
        || ($clar['sender'] == NULL && $clar['recipient'] == NULL)
    );
}

/**
 * Output a single clarification.
 * Helperfunction for putClarification, do _not_ use directly!
 */
function putClar($clar)
{
    // $clar['sender'] is set to the team ID, or empty if sent by the jury.
    if (!empty($clar['sender'])) {
        $from = htmlspecialchars($clar['fromname'] . ' (t' . $clar['sender'] . ')');
    } else {
        $from = 'Jury';
        if (IS_JURY) $from .= ' (' . htmlspecialchars($clar['jury_member']) . ')';
    }
    if ($clar['recipient'] && empty($clar['sender'])) {
        $to = htmlspecialchars($clar['toname'] . ' (t' . $clar['recipient'] . ')');
    } else {
        $to = ($clar['sender']) ? 'Judge' : 'All';
    }

    echo "<table style='width: 100%'>";

    echo '<tr><td>From:</td><td>';
    if (IS_JURY && $clar['sender']) {
        echo '<a href="team.php?id=' . urlencode($clar['sender']) . '">' .
            $from . '</a>';
    } else {
        echo $from;
    }
    echo "</td></tr>\n";

    echo '<tr><td>To:</td><td>';
    if (IS_JURY && $clar['recipient']) {
        echo '<a href="team.php?id=' . urlencode($clar['recipient']) . '">' .
            $to . '</a>';
    } else {
        echo $to;
    }
    echo "</td></tr>\n";

    echo '<tr><td>Subject:</td><td>';
    if (is_null($clar['probid'])) {
        echo "General issue";
    } else {
        if (IS_JURY) {
            echo '<a href="problem?id=' . urlencode($clar['probid']) . '">' .
                'Problem ' . $clar['shortname'] . ": " . $clar['probname'] . '</a>';
        } else {
            echo 'Problem ' . $clar['shortname'] . ": " . $clar['probname'];
        }
    }
    echo "</td></tr>\n";

    echo '<tr><td>Time:</td><td>';
    echo printtime($clar['submittime']);
    echo "</td></tr><tr></tr>";

    echo '<tr><td colspan="2" class="filename">';
    echo '<pre class="well well-lg">' . htmlspecialchars(wrap_unquoted($clar['body'], 80)) . "</pre>";
    echo "</td></tr>\n";

    echo "</table>\n";

    return;
}

/**
 * Output a clarification (and thread) for id $id.
 */
function putClarification($id, $team = NULL)
{
    if ($team == NULL && !IS_JURY) {
        error("access denied to clarifications: you seem to be team nor judge");
    }

    global $DB;

    $clar = $DB->q('TUPLE SELECT * FROM clarification WHERE clarid = %i', $id);

    $clars = $DB->q('SELECT c.*, p.shortname, p.name AS probname, t.name AS toname, f.name AS fromname
	                 FROM clarification c
	                 LEFT JOIN problem p ON (c.probid = p.probid AND p.allow_submit = 1)
	                 LEFT JOIN team t ON (t.teamid = c.recipient)
	                 LEFT JOIN team f ON (f.teamid = c.sender)
	                 WHERE c.respid = %i OR c.clarid = %i
	                 ORDER BY c.submittime, c.clarid',
        $clar['clarid'], $clar['clarid']);

    while ($clar = $clars->next()) {
        // check permission to view this clarification
        if (IS_JURY || canViewClarification($team, $clar)) {
            setClarificationViewed($clar['clarid'], $team);
            putClar($clar);
            echo "<br />\n\n";
        }
    }
}

/**
 * Summarize a clarification.
 * Helper function for putClarificationList.
 */
function summarizeClarification($body)
{
    // when making a summary, try to ignore the quoted text, and
    // replace newlines by spaces.
    $split = explode("\n", $body);
    $newbody = '';
    foreach ($split as $line) {
        if (strlen($line) > 0 && $line{0} != '>') $newbody .= $line . ' ';
    }
    return htmlspecialchars(str_cut((empty($newbody) ? $body : $newbody), 80));
}

/**
 * Print a list of clarifications in a table with links to the clarifications.
 */
function putClarificationList($clars, $team = NULL)
{
    global $username;
    if ($team == NULL && !IS_JURY) {
        error("access denied to clarifications: you seem to be team nor admin");
    }

    echo "<table class=\"list sortable table\" style='width:90%'>\n<thead>\n<tr>" .
        (IS_JURY ? "<th scope=\"col\">ID</th>" : "") .
        "<th scope=\"col\">time</th>" .
        "<th scope=\"col\">from</th>" .
        "<th scope=\"col\">to</th><th scope=\"col\">subject</th>" .
        "<th scope=\"col\">text</th>" .
        (IS_JURY ? "<th scope=\"col\">answered</th><th scope=\"col\">by</th>" : "") .
        "</tr>\n</thead>\n<tbody>\n";

    while ($clar = $clars->next()) {
        // check viewing permission for teams
        if (!IS_JURY && !canViewClarification($team, $clar)) continue;

        $clar['clarid'] = (int)$clar['clarid'];
        $link = '<a  href="clarification?id=' . urlencode($clar['clarid']) . '">';

        if (isset($clar['unread'])) {
            echo '<tr class="unread">';
        } else {
            echo '<tr>';
        }

        if (IS_JURY) {
            echo '<td>' . $link . $clar['clarid'] . '</a></td>';
        }

        echo '<td>' . $link . printtime($clar['submittime']) . '</a></td>';

        if ($clar['sender'] == NULL) {
            $sender = 'Judge';
            if ($clar['recipient'] == NULL) {
                $recipient = 'All';
            } else {
                $recipient = htmlspecialchars($clar['toname']);
            }
        } else {
            $sender = htmlspecialchars($clar['fromname']);
            $recipient = 'Judge';
        }

        echo '<td>' . $link . $sender . '</a></td>' .
            '<td>' . $link . $recipient . '</a></td>';

        echo '<td>' . $link;
        if (is_null($clar['probid'])) {
            echo "general";
        } else {
            echo "problem " . $clar['shortname'];
        }
        echo "</a></td>";

        echo '<td class="clartext">' . $link .
            summarizeClarification($clar['body']) . "</a></td>";

        if (IS_JURY) {
            unset($answered, $jury_member);
            $claim = FALSE;
            $answered = printyn($clar['answered']);
            if (empty($clar['jury_member'])) {
                $jury_member = '&nbsp;';
            } else {
                $jury_member = htmlspecialchars($clar['jury_member']);
            }
            if (!$clar['answered']) {
                if (empty($clar['jury_member'])) {
                    $claim = TRUE;
                } else {
                    $answered = 'claimed';
                }
            }

            echo "<td>$link $answered</a></td><td>";
            if ($claim && isset($clar['sender'])) {
                echo "<a class=\"button\" href=\"clarification?claim=1&amp;id=" .
                    htmlspecialchars($clar['clarid']) . "\">claim</a>";
            } else {
                if (!$clar['answered'] && $jury_member == $username) {
                    echo "<a class=\"button\" href=\"clarification?unclaim=1&amp;id=" .
                        htmlspecialchars($clar['clarid']) . "\">unclaim</a>";
                } else {
                    echo "$link $jury_member</a>";
                }
            }
            echo "</td>";
        }
        echo "</tr>\n";
    }
    echo "</tbody>\n</table>\n\n";
}

/**
 * Output a form to send a new clarification.
 * Set respid to a teamid, to make only that team (or ALL) selectable.
 */
function putClarificationForm($action, $cid, $respid = NULL)
{
    if (empty($cid)) {
        echo '<p class="nodata">No active contest</p>';
        return;
    }

    require_once('forms.php');

    global $DB, $cdata;
    ?>

    <?php
    echo addForm($action, 'post', 'sendclar');
    echo "<table>\n";

    if ($respid) {
        $clar = $DB->q('MAYBETUPLE SELECT c.*, t.name AS toname, f.name AS fromname
		                FROM clarification c
		                LEFT JOIN team t ON (t.teamid = c.recipient)
		                LEFT JOIN team f ON (f.teamid = c.sender)
		                WHERE c.clarid = %i', $respid);
    }

    if (IS_JURY) { // list all possible recipients in the "sendto" box
        echo "<tr><td><b><label for=\"sendto\">Send to</label></b></td><td>\n";

        if (!empty($respid)) {
            echo addHidden('id', $respid);
        }

        $options = array('domjudge-must-select' => '(select...)', '' => 'ALL');
        if (!$respid) {
            $teams = $DB->q('KEYVALUETABLE SELECT teamid, name
			                 FROM team
			                 ORDER BY categoryid ASC, team.name COLLATE utf8_general_ci ASC');
            $options += $teams;
        } else {
            if ($clar['sender']) {
                $options[$clar['sender']] =
                    $clar['fromname'] . ' (t' . $clar['sender'] . ')';
            } else if ($clar['recipient']) {
                $options[$clar['recipient']] =
                    $clar['toname'] . ' (t' . $clar['recipient'] . ')';
            }
        }
        echo addSelect('sendto', $options, 'domjudge-must-select', true);
        echo "</td></tr>\n";
    } else {
        echo '<tr><td><b>To:</b></td><td>Judge</td></tr>';
    }

    // Select box for a specific problem (only when the contest
    // has started) or general issue.
    if (difftime($cdata['starttime'], now()) <= 0) {
        $probs = $DB->q('KEYVALUETABLE SELECT probid, CONCAT(shortname, ": ", name) as name
		                 FROM problem WHERE cid = %i AND allow_submit = 1
		                 ORDER BY shortname ASC', $cid);
    } else {
        $probs = array();
    }
    $options = array('general' => 'General issue') + $probs;
    echo "<tr><td><b>Subject:</b></td><td>\n" .
        addSelect('problem', $options, ($respid ? $clar['probid'] : 'general'), true) .
        "</td></tr>\n";
    ?>

    <tr>
        <td><b><label for="bodytext">Text:</label></b></td>
        <td><?php
            $body = "";
            if ($respid) {
                $text = explode("\n", wrap_unquoted($clar['body']), 75);
                foreach ($text as $line) $body .= "> $line\n";
            }
            echo addTextArea('bodytext', $body, 80, 10, 'required class=clar_message');
            ?></td>
    </tr>
    <tr>
        <td colspan="2">
            <div
                style="text-align: center;"><?php echo addSubmit('Send', 'submit', 'return confirmClar()', true, 'class="btn btn-primary"'); ?></div>
        </td>
    </tr>
    </table>
    </form>

<?php

}
