<?php
/**
 * Gives a team the details of a judging of their submission: errors etc.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require_once('init.php');
$title = 'Submission details';
require(LIBWWWDIR . '/header.php');
?>
    <link rel="stylesheet" href="../assets/vendors/highlightjs/styles/rainbow.css">

<?php
$id = getRequestID();
// select also on teamid so we can only select our own submissions
$row = $DB->q('MAYBETUPLE SELECT p.probid, shortname, p.name AS probname, submittime,
               s.valid, l.name AS langname, result, output_compile, verified, judgingid,f.sourcecode
               FROM judging j
               LEFT JOIN submission s USING (submitid)
               LEFT JOIN language   l USING (langid)
               LEFT JOIN problem    p ON (p.probid = s.probid)
               LEFT JOIN submission_file f USING (submitid)
               WHERE j.submitid = %i AND teamid = %i AND j.valid = 1', $id, $teamid);

if (!$row || $row['submittime'] >= $cdata['endtime'] || (dbconfig_get('verification_required', 0) && !$row['verified'])) {
    echo "<p>Submission not found.</p>\n";
    require(LIBWWWDIR . '/footer.php');
    exit;
}

// update seen status when viewing submission
$DB->q("UPDATE judging j SET j.seen = 1 WHERE j.submitid = %i", $id);

?>

    <div class="container main-container">

    <?php if (!$row['valid']) {
        echo "<p>This submission is being ignored.<br />\n" .
            "It is not used in determining your score.</p>\n\n";
    } ?>


    <div class="row">
        <div class="col-md-6" style="border-right: 1px solid #c0c0c0;padding-right: 10px;">
            <h2>Submission details</h2>
            <table>
                    <tr>
                        <td>Problem:</td>
                        <td><span class="probid"><?php echo htmlspecialchars($row['shortname']) ?>
                                - <?php echo htmlspecialchars($row['probname']) ?></span></td>
                    </tr>
                    <tr>
                        <td>Submitted:</td>
                        <td><?php echo printtime($row['submittime']) ?></td>
                    </tr>
                    <tr>
                        <td>Language:</td>
                        <td><?php echo htmlspecialchars($row['langname']) ?></td>
                    </tr>
                </table>
                <p>Result: <?php echo printresult($row['result'], TRUE) ?></p>

        </div>

        <div class="col-md-6">
            <?php
            $show_compile = dbconfig_get('show_compile', 2);

            if (($show_compile == 2) ||
                ($show_compile == 1 && $row['result'] == 'compiler-error')
            ) {

                echo "<h2>Compilation output</h2>\n\n";

                if (strlen(@$row['output_compile']) > 0) {
                    echo "<pre class=\"output_text\">\n" .
                        htmlspecialchars(@$row['output_compile']) . "\n</pre>\n\n";
                } else {
                    echo "<p class=\"nodata\">There were no compiler errors or warnings.</p>\n";
                }

                if ($row['result'] == 'compiler-error') {
                    echo "<p class=\"compilation-error\">Compilation failed.</p>\n";
                } else {
                    echo "<p class=\"compilation-success\">Compilation successful.</p>\n";
                }
            } else {
                echo "<p class=\"nodata\">Compilation output is disabled.</p>\n";
            }

            $show_sample = dbconfig_get('show_sample_output', 0);

            if ($show_sample && @$row['result'] != 'compiler-error') {
                $runs = $DB->q('SELECT r.*, t.rank, t.description FROM testcase t
	                LEFT JOIN judging_run r ON ( r.testcaseid = t.testcaseid AND
	                                             r.judgingid = %i )
	                WHERE t.probid = %i AND t.sample = 1 ORDER BY rank',
                    $row['judgingid'], $row['probid']);

                $runinfo = $runs->gettable();
                echo '<h3>Run(s) on the provided sample data</h3>';

                if (count($runinfo) == 0) {
                    echo "<p class=\"nodata\">No sample cases available.</p>\n";
                }

                foreach ($runinfo as $run) {
                    echo "<h4 id=\"run-$run[rank]\">Run $run[rank]</h4>\n\n";
                    if ($run['runresult'] === NULL) {
                        echo "<p class=\"nodata\">Run not finished yet.</p>\n";
                        continue;
                    }
                    echo "<table>\n" .
                        "<tr><td>Description:</td><td>" .
                        htmlspecialchars($run['description']) . "</td></tr>" .
                        "<tr><td>Runtime:</td><td>$run[runtime] sec</td></tr>" .
                        "<tr><td>Result: </td><td><span class=\"sol sol_" .
                        ($run['runresult'] == 'correct' ? '' : 'in') .
                        "correct\">$run[runresult]</span></td></tr>" .
                        "</table>\n\n";
                    echo "<h5>Program output</h5>\n";
                    if (@$run['output_run']) {
                        echo "<pre class=\"output_text\">" .
                            htmlspecialchars($run['output_run']) . "</pre>\n\n";
                    } else {
                        echo "<p class=\"nodata\">There was no program output.</p>\n";
                    }
                    echo "<h5>Diff output</h5>\n";
                    if (@$run['output_diff']) {
                        echo "<pre class=\"output_text\">";
                        echo parseRunDiff($run['output_diff']);
                        echo "</pre>\n\n";
                    } else {
                        echo "<p class=\"nodata\">There was no diff output.</p>\n";
                    }
                    echo "<h5>Error output (info/debug/errors)</h5>\n";
                    if (@$run['output_error']) {
                        echo "<pre class=\"output_text\">" .
                            htmlspecialchars($run['output_error']) . "</pre>\n\n";
                    } else {
                        echo "<p class=\"nodata\">There was no stderr output.</p>\n";
                    }
                }
            }
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
                <pre class="code">
                    <code class=""><?php echo htmlspecialchars($row['sourcecode']) ?></code>
                </pre>
        </div>
    </div>

    <script src="../assets/vendors/highlightjs/highlight.pack.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
<?php
require(LIBWWWDIR . '/footer.php');
