<?php

require('init.php');

// one bar per 10 minutes, should be in config somewhere
$bar_size = 10;

$title = "Statistics";
if (!empty($_GET['probid'])) {
    $title .= " - Problem " . $DB->q('VALUE SELECT shortname FROM problem WHERE probid = %i', $_GET['probid']);
}

require(LIBWWWDIR . '/header.php');
echo "<h1>" . htmlspecialchars($title) . "</h1>\n\n";

$res = $DB->q('SELECT result,
		   COUNT(result) as count,
		   (c.freezetime IS NOT NULL && submittime >= c.freezetime) AS afterfreeze,
		   (FLOOR(submittime - c.starttime) DIV %i) * %i AS minute
		   FROM submission s
		   JOIN judging j ON(s.submitid=j.submitid AND j.valid=1)
		   LEFT OUTER JOIN contest c ON(c.cid=s.cid)
		   LEFT OUTER JOIN team t ON(s.teamid=t.teamid)
		   WHERE s.cid = %i AND s.valid = 1 AND t.categoryid = 2 ' .
    (empty($_GET['probid']) ? '%_' : 'AND s.probid = %i ') .
    'AND submittime < c.endtime AND submittime >= c.starttime
    GROUP BY minute, result', $bar_size * 60, $bar_size, $cid, @$_GET['probid']);

// All problems
$problems = $DB->q('SELECT p.probid,p.name FROM problem p WHERE p.cid = %i ORDER BY p.shortname', $cid);
print '<p>';
print '<a href="statistics.php">All problems</a>&nbsp;&nbsp;&nbsp;';
while ($row = $problems->next()) {
    print '<a href="statistics.php?probid=' . urlencode($row['probid']) . '">' . htmlspecialchars($row['name']) . '</a>&nbsp;&nbsp;&nbsp;';
}
print '</p>';

// Contest information
$start = $cdata['starttime'];
$end = $cdata['endtime'];
$length = ($end - $start) / 60;
?>


<div id="placeholder" style="width:1000px;height:400px;"></div>


<?php require(LIBWWWDIR . '/footer.php'); ?>

<!--[if lte IE 8]>
<script language="javascript" type="text/javascript" src="../assets/vendors/flot/excanvas.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="../assets/vendors/flot/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="../assets/vendors/flot/jquery.flot.stack.js"></script>

<script id="source">

    var data = <?= json_encode($res->gettable()); ?>;
    var contestlen = <?= $length; ?>;

    $(function () {
        var answers = [
            {label: "correct", color: "#01DF01", bars: { fill: 1 } },
            {label: "wrong-answer", color: "red", bars: { fill: 0.6} },
            {label: "timelimit", color: "orange", bars: { fill: 0.6} },
            {label: "run-error", color: "#FF3399", bars: { fill: 0.6} },
            {label: "compiler-error", color: "blue", bars: { fill: 0.6 }, },
            {label: "no-output", color: "purple", bars: { fill: 0.6 } },
        ];
        var charts = [];
        for (var i = 0; i < answers.length; i++) {
            var cur = [];
            for (var j = 0; j < contestlen / <?= $bar_size ?>; j++)
                cur.push([j * <?= $bar_size ?> +0.1 * <?= $bar_size ?>, 0]);
            var answer = answers[i].label;
            for (var j = 0; j < data.length; j++) {
                if (data[j].result == answer) {
                    cur[parseInt(data[j].minute) / <?= $bar_size ?>][1] = parseInt(data[j].count);
                }
            }
            var newchart = answers[i];
            newchart.data = cur;
            charts.push(newchart);
        }
        $.plot($("#placeholder"), charts, {
            xaxis: { min: 0, max: contestlen },
            legend: { position: "nw" },
            series: {
                bars: { show: true, barWidth: <?= $bar_size * 0.8 ?>, lineWidth: 0 },
                stack: 0
            }
        });
    });
</script>