<?php require_once('init.php'); ?>

<div class="container">
    <div class="pull-right">
        <a href="../public/scoreboard" class="btn">view public scoreboard</a>
        <?php if (homepage_url): ?>
            <a href="<?php echo homepage_url?>" class="btn">Go back to homepage</a>
        <?php endif ?>
    </div>
</div>

