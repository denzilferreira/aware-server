<h1>Plugins</h1>

<input class="search" id="developers-search" placeholder="Search">

<table id="developers" class="tablesorter">
	<thead> 
	<tr id="title">
		<th>NAME</th>
		<th>EMAIL</th>
		<th colspan="2"># OF PLUGINS</th>
	</tr>
	</thead>
	
	<tbody>
	<?php
		foreach($developers as $developer) {
			echo	"<tr class='developer toggle' id='" . $developer['id'] . "'>".
						"<td class='name'>" .$developer['last_name'] . ", " . $developer['first_name'] .  "</td>".
						"<td class='email'>" . $developer['email'] . "</td>".
						"<td class='plugins'>" . $developer['plugins_count'] . "</td>".
						"<td class='empty'></td>".
					"</tr>".
					
					"<tr class='tablesorter-childRow sub'>".
						"<td class='plugin-name'>Plugin name</td>".
						"<td class='plugin-desc'>Description</td>".
						"<td class='plugin-type'>Plugin type</td>".
						"<td class='plugin-status'>Status</td>".
					"</tr>";
					
			
					
			foreach($plugins as $plugin) { //id	title	desc creator_id
				if ($plugin['creator_id'] == $developer['id']) {
					$type = $plugin['type']==0 ? 'plugin' : 'sensor'; // $type is plugin if it's 0 in the db and otherwise is a sensor
					$status = $plugin['status']>0 ? 'accepted' : ($plugin['status']==0 ? 'pending' : 'declined'); // $status is accepted if it's 1, declined is -1 and pending is 0.
					echo	"<tr class='tablesorter-childRow'>".
									"<td class='plugin-name'>" . anchor('developer/plugin/' . $plugin['id'], $plugin['title'], array('class' => 'plugin-link')) . "</td>".
									"<td class='plugin-desc' title='" . html_escape($plugin['desc']) . "'>" . html_escape($plugin['desc'])  . "</td>".
									"<td class='plugin-type'>". $type ."</td>". //these are hardcoded now, FIX THIS!!
									"<td class='plugin-status'>". $status ."</td>".
							"</tr>";
				
				} else if ($developer['plugins_count'] == 0) {
					echo 	"<tr class='tablesorter-childRow'>
								<td class='no-plugins' colspan='3'>The user has no plugins</td>
							</tr>";
					break;
				}
			}
			
					
		}
		
	?>
	</tbody>
	
</table>