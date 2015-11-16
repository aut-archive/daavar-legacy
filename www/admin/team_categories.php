<?php
/**
 * View the categories
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$title = 'Categories';

require(LIBWWWDIR . '/header.php');

echo "<h1>Categories</h1>\n\n";

if (IS_ADMIN) {
    echo '<a href="team_category?cmd=add" class="btn pull-right" > Add New Category </a> <br><br>';
}


$res = $DB->q('SELECT team_category.*, COUNT(teamid) AS numteams
               FROM team_category LEFT JOIN team USING (categoryid)
               GROUP BY team_category.categoryid ORDER BY sortorder, categoryid');

if ($res->count() == 0) {
    echo "<p class=\"nodata\">No categories defined</p>\n\n";
} else {
    echo "<table class=\"table list sortable\">\n<thead>\n" .
        "<tr><th scope=\"col\">ID</th><th scope=\"col\">Sort</th>" .
        "<th scope=\"col\">Name</th><th scope=\"col\">#Teams</th>" .
        "<th scope=\"col\">Visible</th><th></th></tr>\n" .
        "</thead>\n<tbody>\n";

    while ($row = $res->next()) {
        $link = '<a href="team_category.php?id=' . (int)$row['categoryid'] . '">';
        echo '<tr' . (isset($row['color']) ? ' style="background: ' .
                $row['color'] . ';"' : '') .
            '><td>' . $link . (int)$row['categoryid'] .
            '</a></td><td>' . $link . (int)$row['sortorder'] .
            '</a></td><td>' . $link . htmlspecialchars($row['name']) .
            '</a></td><td class="tdright">' . $link . (int)$row['numteams'] .
            '</a></td><td class="tdcenter">' . $link . printyn($row['visible']) .
            '</a></td>';
        if (IS_ADMIN) {
            echo "<td class=\"editdel\">" .
                editLink('team_category', $row['categoryid']) . " " .
                delLink('team_category', 'categoryid', $row['categoryid']) . "</td>";
        }
        echo "</tr>\n";
    }
    echo "</tbody>\n</table>\n\n";
}

require(LIBWWWDIR . '/footer.php');
