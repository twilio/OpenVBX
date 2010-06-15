<?php if(!is_file($script)): ?>

<h1>Unable to load script <?php echo $script ?> - file not found</h1>

<?php else: ?>

<?php include_once($script); ?>

<?php endif; ?>
