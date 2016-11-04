<?php
function gen_s4rislog()
{
    global $cdata, $DB;
    $d = array();

    $d['contestName'] = $cdata['contestname'];

    $d['freezeTimeMinutesFromStart'] = 300; //TODO !

    //Problem letters
    $d['problemLetters'] = ($DB->q('SELECT shortname FROM problem')->getcolumn());

    //Contestants
    $d['contestants'] = ($DB->q('SELECT name FROM team')->getcolumn());

    //Runs

    $contestStarttime = $cdata['starttime'];

    $d['runs'] = $DB->q("
    SELECT team.name AS contestant ,
    problem.shortname AS problemLetter , submission.submitid AS success ,
    round((submission.submittime-$contestStarttime)/60) AS timeMinutesFromStart
    FROM submission
    LEFT JOIN team USING (teamid)
    LEFT JOIN problem USING (probid)
    ")->gettable();


    foreach ($d['runs'] as &$run) {

        $id = $run['success'];

        $run['success'] = $DB->q("
            SELECT result FROM `judging` J JOIN submission S
            ON J.submitid = S.submitid
            WHERE J.submitid=%i
            ORDER BY J.judgingid DESC LIMIT 0,1
        ", $id)->getcolumn('result')[0] == 'correct';


    }


    return $d;
}