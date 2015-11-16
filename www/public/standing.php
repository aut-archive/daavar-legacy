<?php
require_once 'init.php';

?>

<html>
<head>
    <link href="../assets/vendors/s4ris/styles/style.css" type="text/css" rel="stylesheet" media="screen">
    <link href="../assets/css/style_standing.css" type="text/css" rel="stylesheet" media="screen">

    <script src="../assets/vendors/s4ris/scripts/jquery.min.js"></script>
    <script src="../assets/vendors/s4ris/scripts/jquery.localscroll-1.2.7-min.js"></script>
    <script src="../assets/vendors/s4ris/scripts/jquery.scrollTo-1.4.2-min.js"></script>
    <script src="../assets/vendors/s4ris/scripts/jquery.easing.1.3.js"></script>
    <script src="../assets/vendors/s4ris/scripts/jquery.animate-colors.js"></script>
    <script src="../assets/vendors/s4ris/scripts/ejudge.convertor.js"></script>
    <script src="../assets/vendors/s4ris/scripts/l18n.js"></script>

    <script src="../assets/vendors/s4ris/scripts/main.js"></script>

    <meta http-equiv=Content-Type content="text/html; charset=windows-1251">
    <meta HTTP-EQUIV="pragma" CONTENT="no-cache">
    <meta HTTP-EQUIV="Cache-Control" CONTENT="no-cache">

    <title>AUTJudge - S4RiS StanD</title>
</head>
<body>
<div id="standings-table" style="position:relative; overflow:hidden;"></div>
</body>
</html>


<script>
    function init(data) {
        console.log(data);
        //number of participants simultaneously displayed
        frameSize = 5;//5...9
        //number of participants displayed above "unfreezes"
        topLimit = 1; //0...4
        //NOTE : topLimit <= frameSize

        var defrostingComparatorName = 'alphabeticProblemOrder';//or increasingLastSubmitTime

        var contest = new Contest(data, defrostingComparatorName);

        var standings = contest.createFrozenStandings();
        var problemsHash = contest.getProblems();
        var limit = contest.getTimeBeforeFreeze();
        var problemsList = new Array();
        setContestCaption(contest.getContestName());
        for (var letter in problemsHash) {
            problemsList[problemsList.length] = problemsHash[letter].name;
        }
        problemsList.sort();
        var size = standings.size();
        setAdaptiveSize(frameSize);
        for (var i = 0; i < size; i++) {
            createTemplateRow(i);
            fillProblemNames(problemsList);
            setContestantName(i, standings.get(i).name);
            setContestantRunInfo(i, standings.get(i), limit);
        }
        setAdaptiveRow(rowHeight);
        standings.updatePlaces();
        // hide control panel
        JSONLogPanelControl();
        // go to the bottom of the table
        standings.setCurTopRow(size - frameSize);
        setTopRow(standings.getCurTopRow(), 4000, {easing: 'easeInQuad'});
        // setup current row
        standings.setCurRow(size - 1);
        standings.setCurFrameRow(frameSize - 1);
        setCurrentRow(standings.getCurRow());
        document.onkeydown = function (e) {
            if (e.which == 78) { // process "Next Step". Key "N".
                standings.goNext();
            } else if (e.which == 70) { // process "Fast Next Step". Key "F".
                standings.goFFNext();
            } else if (e.which == 66) { // move current row down. Key "B".
                standings.goBack();
            }
        };


    }

    $('document').ready(function () {
        $('#inner #show-log').click(JSONLogPanelControl);
        <?php if(isset($_REQUEST['ajax'])):?>
        $.ajax('../../api/S4risLog').success(function (data) {
            init(data);
        });
        <?php else : ?>
        data = <?php
         require_once 'init.php';
         require_once(LIBWWWDIR . '/s4rislog.php');
         echo json_encode(gen_s4rislog());
        ?>;
        init(data);
        <?php endif ?>
    });

</script>

<?php
echo "Powered by S4ris - Autjudge";