<?php
require_once('init.php');

$unread = $DB->q("SELECT COUNT(*) AS c FROM team_unread WHERE teamid=%i", $teamid)->next()['c'];

if (isset($_REQUEST['ajax'])) {
    switch ($_REQUEST['ajax']) {
        case 'unread':
            echo $unread;
            break;
    }
    exit;
}
?>

    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <a href="." class="navbar-brand"><?php echo  $cdata['contestname']; ?></a>
                <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="navbar-collapse collapse" id="navbar-main">
                <ul class="nav navbar-nav">
                    <li class="<?php if ($currPage == 'index') echo 'active' ?>">
                        <a href="." accesskey="d">Dashboard</a>
                    </li>
                    <?php if (have_problemtexts() && calcFreezeData($cdata)['cstarted']) {
                        $res = $DB->q('SELECT p.probid,p.shortname,p.name,p.color FROM problem p WHERE cid = %i AND allow_submit = 1 AND
		                                    problemtext_type IS NOT NULL ORDER BY p.shortname', $cid);
                        if ($res->count() > 0) {
                            ?>
                            <li class="dropdown  <?php if ($currPage == 'problem') echo 'active' ?>">
                                <a href="submissions" role="button" class="dropdown-toggle"
                                   data-toggle="dropdown">
                                    Problems<i class="caret"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php while ($row = $res->next()) {
                                        print '<li> ' .
                                            '<a href="problem?id=' . urlencode($row['probid']) . '">' .
                                            htmlspecialchars($row['shortname']) . ' (' .
                                            htmlspecialchars($row['name']) . ")</a></li>\n";
                                    } ?>
                                </ul>
                            </li>
                            <?php
                        }
                    } ?>

                    <li class="<?php if ($currPage == 'submit') echo 'active' ?>">
                        <a href="submit">Submit</a>
                    </li>

                    <li class="<?php if ($currPage == 'submissions') echo 'active' ?>">
                        <a href="submissions">Submissions</a>
                    </li>

                    <li class="<?php if ($currPage == 'scoreboard') echo 'active' ?>">
                        <a href="scoreboard">Scoreboard</a>
                    </li>

                    <li class="<?php if ($currPage == 'clarification') echo 'active' ?>">
                        <a href="clarification">Clarifications
                                <span class="badge badge-warning"
                                      id="unread" <?php if (!$unread) echo 'style="display: none"' ?>>
                                    <?php echo $unread ? $unread : '' ?>
                                </span>
                            <script>
                                var doReloadUnread = true;
                            </script>
                        </a>

                    </li>

                    <?php if (have_printing()): ?>
                        <li class="<?php if ($currPage == 'print') echo 'active' ?>">
                            <a href="print">Print source</a>
                        </li>
                    <?php endif ?>

                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a><?php putClock() ?></a></li>
                    <li class="dropdown">
                        <a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown"> <i
                                class="icon-user"></i> <?php echo $userdata['username'] ?> <i class="caret"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (AllowEditUserProfile): ?>
                                <li><a href="edit_profile">Edit profile</a></li>
                                <li class="divider"></li>
                            <?php endif ?>
                            <li><a tabindex="-1" href="../auth/logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>



<?php

echo "<div id=\"menutopright\">\n";

//putClock();

echo "</div></nav>\n\n";
