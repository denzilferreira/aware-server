<br>
<div class='<?php if (!isset($level)) { echo "danger"; } else { echo $level; } ?>'>
<?php echo "<b>ERROR: </b> "; if (!isset($text)) { echo "Unknown error occurred."; } else { echo $text; } ?>
</div>