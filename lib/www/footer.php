<script src="../assets/vendors/jquery.min.js"></script>

<script src="../assets/vendors/bootstrap/js/bootstrap.min.js"></script>

<script src="../assets/vendors/jquery.plugin.min.js"></script>

<script src="../assets/vendors/jquery.countdown.min.js"></script>
<script src="../assets/vendors/jGrowl/jquery.jgrowl.js"></script>

<script type="text/javascript" src="../assets/js/scripts_ready.js"></script>


<div class="pusher"></div>

</div>
</div>


<footer class="footer">
    <a href="#"><?php echo productName ?> v<?php echo version ?></a> by <a href="mailto:pooya@pi0.ir">pi0</a>
</footer>


</body>
</html>


<?php

if (file_exists('footer.custom.php'))
    require_once 'footer.custom.php';

?>