<?php

require_once 'init.php';
global $cdata;

$fdata = calcFreezeData($cdata);

if (!$cdata['enabled'] || $fdata['cstarted']) {
    header('Location: ..');
    exit;
}

$diff = difftime($cdata['starttime'], now());

?>
<html>
<head>

    <link href="../assets/vendors/textillate/assets/animate.css" rel="stylesheet">
    <link href="../assets/vendors/textillate/assets/style.css" rel="stylesheet">

    <link rel="stylesheet" href="../assets/vendors/FlipClock/compiled/flipclock.css">

    <link rel="stylesheet" href="../assets/css/style_countdown.css">

    <script src="../assets/vendors/jquery-1.9.1.min.js"></script>

    <script src="../assets/vendors/FlipClock/compiled/flipclock.min.js"></script>

    <script src="../assets/vendors/textillate/assets/jquery.fittext.js"></script>
    <script src="../assets/vendors/textillate/assets/jquery.lettering.js"></script>
    <script src="../assets/vendors/textillate/jquery.textillate.js"></script>

    <script>
        hljs.initHighlightingOnLoad();
    </script>

    <script>

    </script>

    <!--    <link href='http://fonts.googleapis.com/css?family=Rokkitt' rel='stylesheet' type='text/css'>-->

</head>
<body>

<div class="header">
    <h1 class="glow in tlt">AUT ACM Contest 2014</h1>
</div>


<div class="countdown">
    <div class="clock" style="margin:2em;"></div>
</div>

<div class="footer">
    <div>
        <h1 style="visibility: hidden">Lets go ...</h1>
    </div>
</div>


<script type="text/javascript">


    var clock;

    $(document).ready(function () {

        var diff = <?php echo $diff?>;

        // Instantiate a coutdown FlipClock
        clock = $('.clock').FlipClock(diff, {
            clockFace: 'MinuteCounter',
            countdown: true
        });

        setTimeout(function () {
            $(".footer h1")
                .fitText(2)
                .textillate({ in: {
                    effect: 'fadeIn',
                    callback: function () {
//                        window.location='..';
                    }
                }});
        }, diff * 1000);


        $(".header h1")
            .fitText(1)
            .textillate({ in: { effect: 'flipInY' }});


    });


</script>

</body>
</html>