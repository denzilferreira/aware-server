<h1>My studies</h1>

<?php 
if (count($studies_topics) == 0) {
			echo "<div class='errormessage'>You don't have any studies. To create one, press 'Create new' on the right.</div>";
			echo "<div id='create-new-study'>";
			echo anchor('researcher/new_study', 'Create new', array('class' => 'top-link'));
			echo "</div>";
	}
else { // print the studies if there are some.
?>
<div id="search-element">
<input class="search" id="researchers-search" placeholder="Search">
</div>

<div id="create-new-study">
<?php echo anchor('researcher/new_study', 'Create study', array('class' => 'top-link')); ?>
</div>
<table id="researcher-studies" class="tablesorter">
	<thead> 
	<tr id="title">
		<td width="350">NAME</td>
		<td width="100">CREATED</td>
		<td>DESCRIPTION</td>

	</tr>
	</thead> 
	<tbody> 
<?php

$datestring = "%d.%m.%Y";

	foreach($studies_topics as $study) {
		
		if ($study["status"]==1) {

			echo "<tr class='study' id='" . $study['id'] . "'>";
			echo "<td class='name' width='225'>" . anchor('researcher/study/' . $study['id'], $study['study_name'], array('class' => 'study-link')) .  "</td>";
		}
		else {
			echo "<tr class='study closed' id='" . $study['id'] . "'>";
			echo "<td class='name' width='225'>" . anchor('researcher/study/' . $study['id'], $study['study_name'], array('class' => 'study-link')) .  " (closed) </td>";
			//#F2F2F2
			
		}
		echo		"<td class='created'>" . gmdate("d.m.Y", $study["created"]) . "</td>".
					"<td class='description'><div class='description' >" . strip_tags($study['description']) . "</div></td>".
				"</tr>";
		
	} //foreach ends
} // else ends
?>
	</tbody>
</table>