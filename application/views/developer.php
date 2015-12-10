<meta charset="utf-8" />
<h1>My plugins</h1>

<?php
if (count($plugins_topics) == 0) {
			echo "<div class='errormessage'>You don't have any plugins or sensors. To create one, press 'add new' on the right.</div>";
			echo "<div id='create-new-plugin'>";
			echo anchor('developer/edit_plugin/new/0', 'Add new', array('class' => 'top-link'));
			echo "</div>";
}
else { // print the plugins if there are some.
?>

<div id="search-element">
<input class="search" id="developers-search" placeholder="Search">
</div>

<div id="create-new-plugin">
<?php echo anchor('developer/edit_plugin/new/0', 'Add new', array('class' => 'top-link')); ?>
</div>
<table id="developer-plugins" class="tablesorter">
	<thead> 
	<tr id="title">
		<td>NAME</td>
		<td>CREATED</td>
		<td>DESCRIPTION</td>
		<td>TYPE</td>
		<td>STATUS</td>
	</tr>
	</thead> 
	<tbody> 
<?php
	foreach($plugins_topics as $plugin) {
		$type = $plugin['type']==0 ? 'plugin' : 'sensor'; // $type is plugin if it's 0 in the db and otherwise is a sensor
		$status = $plugin['status']>0 ? 'accepted' : ($plugin['status']==0 ? 'pending' : 'declined'); // $status is accepted if it's 1, declined is -1 and pending is 0.
		echo "<tr class='plugin' id='" . $plugin['id'] . "'>";
		echo 	"<td class='name' width='225'>" . anchor('developer/plugin/' . $plugin['id'], $plugin['title'], array('class' => 'plugin-link')) .  "</td>";
		echo	"<td class='created' width='100'>" . gmdate("d.m.Y", $plugin["created_date"]) . "</td>".
				"<td class='description'>" . $plugin['desc'] . "</div></td>".
				"<td class='type' width='80'>". $type ."</td>".
				"<td class='status' width='80'>". $status ."</td>".
			"</tr>";
		
	} //foreach ends
} // else ends
?>

	</tbody>
</table>