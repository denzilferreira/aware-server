<div style="margin-bottom:20px">
<?php
if ($this->uri->segment(2) == 'new_plugin') {
	echo 'Successfully created a new plugin!';
}
else if ($this->uri->segment(2) == 'edit_plugin') {
	echo 'Successfully updated the plugin data!';
}
else echo "Success.";
?>
</div>
<div>
<?php echo anchor('developer','Back to developer view');?>
</div>