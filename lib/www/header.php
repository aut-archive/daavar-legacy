<?php
/**
 * Common page header.
 * Before including this, one can set $title, $refresh,
 * $printercss, $jscolor and $menu.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

header('Content-Type: text/html; charset=' . DJ_CHARACTER_SET);

/* Prevent clickjacking by forbidding framing in modern browsers.
 * Really want to frame DOMjudge? Then change DENY to SAMEORIGIN
 * or even comment out the header altogether. For the public
 * interface there's no risk, and embedding the scoreboard in a
 * frame may be useful.
 */
if (!IS_PUBLIC) header('X-Frame-Options: DENY');

$refresh_cookie = (!isset($_COOKIE["domjudge_refresh"]) || (bool)$_COOKIE["domjudge_refresh"]);

if (isset($refresh) && $refresh_cookie) {
    header('Refresh: ' . $refresh);
}

if (!isset($menu)) {
    $menu = true;
}

?><!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>

    <meta charset="<?php echo DJ_CHARACTER_SET ?>"/>

    <title><?php echo $pagename . '_' . (isset($title) && $title) ? $title : $currPage ?></title>

    <link rel="icon" href="../assets/images/favicon.png" type="image/png"/>

    <link href="../assets/vendors/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">

    <link href="../assets/vendors/jGrowl/jquery.jgrowl.css" rel="stylesheet" media="screen">

    <link href="../assets/css/style.css" rel="stylesheet" media="screen">

    <script type="text/javascript" src="../assets/js/scripts.js"></script>

    <?php
    if (IS_JURY) {

        echo '<link rel="stylesheet" href="../assets/css/style_jury.css" type="text/css" />';

        if (isset($printercss))
            echo '<link rel="stylesheet" href="../assets/css/style_printer.css" type="text/css" media="print" />';

        if (isset($jscolor))
            echo '<script type="text/javascript" src="../assets/vendors/jscolor/jscolor.js"></script>';

        echo '<script type="text/javascript" src="../assets/js/sorttable.js"></script>';
    }

    ?>
</head>
<?php

if (IS_JURY) {
    global $pagename;
    echo "<body onload=\"setInterval('updateMenu(" .
        (int)($pagename == 'clarifications.php' && $refresh_cookie) . ", " .
        (int)($pagename == 'judgehosts.php' && $refresh_cookie) . ")', 20000)\">\n";
} else {
    echo "<body>\n";
}
echo "<div class=page-wrapper>";

/* NOTE: here a local menu.php is included
 *       both jury and team have their own menu.php
 */
if ($menu) include("menu.php");

if (file_exists('header.custom.php'))
    @require_once 'header.custom.php';
