<?php
require_once 'init.php';
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

                <?php if (checkrole('balloon') && !checkrole('jury')) { //Balloon ?>
                    <li class="<?php if ($currPage == 'balloons') echo 'active' ?>">
                        <a href="balloons" accesskey="b">Balloons</a>
                    </li>
                <?php } ?>

                <?php if (checkrole('jury')) { //Jury ?>
                    <li><a href="submissions">Submissions</a></li>
                    <li><a href="clarifications">Clarifications</a></li>

                    <li class="dropdown">
                        <a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">Teams and Users <i
                                class="caret"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="users">Users</a></li>
                            <li><a href="teams">Teams</a></li>
                            <li><a href="team_categories">Team Categories</a></li>
                            <li><a href="team_affiliations">Team Affiliations</a></li>
                            <li><a href="approve">Team Approval</a></li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">Overview <i
                                class="caret"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="scoreboard">Scoreboard</a></li>
                            <li><a href="contests">Contests</a></li>
                            <li><a href="problems">Problems</a></li>
                            <li><a href="balloons">Balloon Status</a></li>
                            <li><a href="executables">Executables</a></li>
                            <li><a href="judgehosts">Judgehosts</a></li>
                            <li><a href="languages">Languages</a></li>
                            <li><a href="statistics">Statistics</a></li>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (IS_ADMIN) { //Admin ?>
                    <li class="dropdown">
                        <a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">Administration <i
                                class="caret"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="config">Configuration settings</a></li>
                            <li><a href="checkconfig">Config checker</a></li>
                            <li><a href="impexp">Import / export</a></li>
                            <li><a href="csvimport">CSV Import</a></li>
                            <li><a href="genpasswds">Manage team passwords</a></li>
                            <li><a href="refresh_cache">Refresh scoreboard cache</a></li>
                            <li><a href="check_judgings">Judging verifier</a></li>
                            <li><a href="auditlog">Activity log</a></li>
                        </ul>
                    </li>
                <?php } ?>

            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li><a><?php putClock() ?></a></li>
                <li class="dropdown">
                    <a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i> <?php echo $userdata['name'] ?> <i class="caret"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <?php if (IS_ADMIN) : ?>
                                <a tabindex="-1" href="user.php?id=<?php echo $userdata['userid'] ?>">Profile</a>
                            <?php endif ?>
                        </li>

                        <li>
                            <?php include LIBWWWDIR.'/notify.php'; ?>
                        </li>

                        <li class="divider"></li>
                        <li>
                            <a tabindex="-1" href="../auth/logout.php">Logout</a>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>
    </div>
</div>

<div class="container main-container">