<?php
/**
 * View the problems
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$title = 'Problems';

require(LIBWWWDIR . '/header.php');

echo "<h1>Problems</h1>\n\n";

// Select all data, sort problems from the current contest on top.
$res = $DB->q('SELECT p.probid,p.shortname,p.name,p.allow_submit,p.allow_judge,p.timelimit,p.color,
               p.problemtext_type, c.*, COUNT(testcaseid) AS testcases
               FROM problem p
               NATURAL JOIN contest c
               LEFT JOIN testcase USING (probid)
               GROUP BY probid ORDER BY (p.cid = %i) DESC, p.cid, probid', $cid);

if ($res->count() == 0) {
    echo "<p class=\"nodata\">No problems defined</p>\n\n";
} else {
    echo "<table class=\"table list sortable\">\n<thead>\n" .
        "<tr><th scope=\"col\">ID</th><th scope=\"col\">shortname</th><th scope=\"col\">name</th>" .
        "<th scope=\"col\" class=\"sorttable_numeric\">contest</th>" .
        "<th scope=\"col\">allow<br />submit</th>" .
        "<th scope=\"col\">allow<br />judge</th>" .
        "<th scope=\"col\">time<br />limit</th>" .
        "<th class=\"sorttable_nosort\" scope=\"col\">colour</th>" .
        "<th scope=\"col\">test<br />cases</th>" .
        "<th scope=\"col\"></th>" .
        "<th scope=\"col\"></th>" .
        (IS_ADMIN ? "<th scope=\"col\"></th>" : '') .
        "</tr></thead>\n<tbody>\n";

    $lastcid = -1;

    while ($row = $res->next()) {
        $classes = array();
        if ($row['cid'] != $cid) $classes[] = 'disabled';
        if ($row['cid'] != $lastcid) {
            if ($lastcid != -1) $classes[] = 'contestswitch';
            $lastcid = $row['cid'];
        }
        $link = '<a href="problem.php?id=' . urlencode($row['probid']) . '">';

        echo "<tr class=\"" . implode(' ', $classes) .
            "\"><td>" . $link . "p" .
            htmlspecialchars($row['probid']) . "</a>" .
            "</td><td class=\"probid\">" . $link . htmlspecialchars($row['shortname']) . "</a>" .
            "</td><td>" . $link . htmlspecialchars($row['name']) . "</a>" .
            "</td><td title=\"" . htmlspecialchars($row['contestname']) . "\">" .
            $link . 'c' . htmlspecialchars($row['cid']) . "</a>" .
            "</td><td class=\"tdcenter\">" . $link .
            printyn($row['allow_submit']) . "</a>" .
            "</td><td class=\"tdcenter\">" . $link .
            printyn($row['allow_judge']) . "</a>" .
            "</td><td>" . $link . (int)$row['timelimit'] . "</a>" .
            "</td>" .
            (!empty($row['color'])
                ? '<td title="' . htmlspecialchars($row['color']) .
                '">' . $link . '<div class="circle" style="background-color: ' .
                htmlspecialchars($row['color']) .
                ';"></div></a>'
                : '<td>' . $link . '&nbsp;</a>');
        echo "</td><td><a href=\"testcase.php?probid=" . $row['probid'] .
            "\">" . $row['testcases'] . "</a></td>";
        if (!empty($row['problemtext_type'])) {
            echo '<td title="view problem description">' .
                '<a href="problem.php?id=' . urlencode($row['probid']) .
                '&amp;cmd=viewtext"><img src="../assets/images/' . urlencode($row['problemtext_type']) .
                '.png" alt="problem text" /></a></td>';
        } else {
            echo '<td></td>';
        }
        if (IS_ADMIN) {
            echo '<td title="export problem as zip-file">' .
                exportLink($row['probid']) . '</td>' .
                "<td class=\"editdel\">" .
                editLink('problem', $row['probid']) . " " .
                delLink('problem', 'probid', $row['probid']) . "</td>";
        }
        echo "</tr>\n";
    }
    echo "</tbody>\n</table>\n\n";
}

if (IS_ADMIN) {
    echo "<p>" . addLink('problem') . "</p>\n\n";
    if (class_exists("ZipArchive")) {
        echo "\n" . addForm('problem.php', 'post', null, 'multipart/form-data') .
            'Problem archive(s): ' .
            addFileField('problem_archive[]', null, ' required multiple accept="application/zip"') .
            addSubmit('Upload', 'upload') .
            addEndForm() . "\n";
    }
}

require(LIBWWWDIR . '/footer.php');
