<h1>Studies</h1>

<input class="search" id="researchers-search" placeholder="Search">

<table id="researchers" class="tablesorter">
	<thead> 
	<tr id="title">
		<th>NAME</th>
		<th>EMAIL</th>
		<th># OF STUDIES</th>
	</tr>
	</thead>
	
	<tbody>
	<?php
	
		foreach($researchers as $researcher) {
			echo	"<tr class='researcher toggle' id='" . $researcher['id'] . "'>".
						"<td class='name'>" .$researcher['last_name'] . ", " . $researcher['first_name'] .  "</td>".
						"<td class='email'>" . $researcher['email'] . "</td>".
						"<td class='studies'>" . $researcher['studies_count'] . "</td>".
					"</tr>".
					
					"<tr class='tablesorter-childRow sub'>".
						"<td class='study-name'>Study name</td>".
						"<td>Database name</td>".
						"<td>Description</td>".
					"</tr>";
					
			foreach($studies as $study) {
				$study_users = preg_split('/,/', $study['users']);
				if (in_array($researcher['id'], $study_users)) {
					echo	"<tr class='tablesorter-childRow'>".
									"<td class='study-name'>" . anchor('researcher/study/' . $study['id'], $study['study_name'], array('class' => 'study-link')) . "</td>".
									"<td>" . $study['db_name']  . "</td>".
									"<td class='study-description' title='" . $study['description']  . "'>" . $study['description']  . "</td>".
							"</tr>";
				
				} else if ($researcher['studies_count'] == 0) {
					echo 	"<tr class='tablesorter-childRow'>
								<td class='no-studies' colspan='3'>User is not part of any study</td>
							</tr>";
					break;
				}
			}
					
		}
		
	?>
	</tbody>
	
</table>
