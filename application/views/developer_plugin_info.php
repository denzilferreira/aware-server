<?php

$delete_img_properties = array(
          'src' => 'application/views/images/delete_icon.png',
          'title' => 'Remove plugin from study',
          'class' => 'del_image',
          'width' => '11',
          'height' => '11'
);
$type = $plugin_data['type']==0 ? 'Plugin':'Sensor';

if ($plugin_data['iconpath'] !== "" || !empty($plugin_data['iconpath'])) {
	echo "<div class='icon'><img src='" . base_url() . $plugin_data['iconpath'] . "' height='130' width='130'></div>";
}
echo "
	<div id='" . $plugin_data['id']. "' class='plugin_id' 'style='display: none;'></div><div class='pluginname'><h1>". $plugin_data['title'] ."</h1>
	<div class='statebox'>
	<input type='checkbox' id='statebox' class='statebox basic'";
	if ($plugin_data['state'] == 1) {
		echo " checked='true' value='1'";
	}
	else {
		echo " value='0'";
	}
	echo ">
	<label for='statebox'>Make my plugin public</label>
	</div>
	<div class='add_study'>
	";
	if (!empty($studyaccess)) {
		echo "<b>This plugin is allowed in following studies:</b>";
		foreach ($studyaccess as $sa) {


			echo "
			<div id='" . $sa["api_key"] . "' class='add_study'>" .
			$sa['study_name'] . " (<i>" . $sa['api_key'] . "</i>)
			<a href='#' class='delete-apikey'>" . img($delete_img_properties) . "</a>
			<div class='delete-dialog'>Are you sure you want remove this plugin from study \"". $sa['study_name'] . "\"?</div></div>";

		}
	}
	echo "<div class='add_study_button'><button class='add_api' id='add_api'>Add this plugin to a study</button></div>

	<div class='add_api_confirm' style='display:none'>
		<input type='text' id='api_key_text'>
	</div

	</div>
	</div></div><div class='buttons'> <div class='edit_button'>". 
	form_open('developer/edit_plugin/edit/'.$plugin_data['id'], array('id'=>'edit_plugin')).
	form_submit('send', 'Edit plugin', 'class="editbutton"').
	form_close();
	echo "</div><div class='remove_button'>".
	form_open('developer/remove_plugin/'.$plugin_data['id'], array('id'=>'remove_plugin')).
	form_submit('send', 'Remove plugin', 'class="editbutton"').
	form_close();
	
	echo "</div></div><div class='package_information'> <div class='dev_titles'>Type:</div>
	<div class='plugin_values'>".$type."</div>";
	if ($manager == "1") {
		echo "<div class='dev_titles'>Developer:</div>
		<div class='plugin_values'><a href='mailto:".$developer_info[0]['email']."' target='_top'>".$developer_info[0]['first_name'] . ' ' . $developer_info[0]['last_name']."</a></div>";
	}
	echo "
	<div class='dev_titles'>Version:</div>
	<div class='plugin_values'>".$plugin_data['version']."</div>
	<div class='dev_titles'>File name:</div>
	";

	if ($plugin_data['package_path'] !== "") {
		echo "<div class='plugin_values'><a href='".$plugin_data['package_path'].$plugin_data['package_name']."'>".$plugin_data['package_name']."</a></div>";
	}
	else {
		echo "<div class='plugin_values'>Package not set!</div>";
	}
	
	echo "<div class='dev_titles'>Git Repository URL:</div>
	<div class='plugin_values'>";

	if ($plugin_data['repository'] !== "") {
		echo $plugin_data['repository'] . "</div>";
	}
	else {
		echo "No repository URL set</div>";
	}

	echo "<div class='dev_titles'>Package:</div>
	<div class='plugin_values'>";
	echo $plugin_data['package'] . "</div>";

	echo "<div class='dev_titles'>Permissions:</div>
	<div class='permissions'>";
	
	if(!empty($permissions)) {
		foreach($permissions as $permission) {
			echo '<div>'.$permission['permission'] . '</div>';
		}
		echo "</div>";
	}
	else {
		echo 'No permissions</div>';
	}
	
echo "</div>
<div class='plugin_information'> 
<br><div class='description'>".$plugin_data['desc']."</div>";

echo "<div class='plugin_description'><h2>Settings:</h2>";

if (!empty($settings)) {
	foreach ($settings as $setting) {
		if (!empty($setting['setting'])) echo "<li class='info'><strong>" . $setting['setting'] . ":</strong> " . $setting['desc'] . "</li>";
	}	
}

else {
	echo "<p>No settings.</p>";
}

echo "
	</div>
	<div class='plugin_description'>
	<img class='alignnone wp-image-1129 size-full' src='http://www.awareframework.com/wp-content/uploads/2014/02/aware-broadcasts.png' alt='aware-broadcasts' width='98' height='109'>";

	if(!empty($broadcasts) && !empty($broadcastextras)) {
		foreach ($broadcasts as $broadcast) {
			echo "
				<li class='info'> <strong>" .$broadcast['broadcast']. ":</strong> " . $broadcast['desc']
			;
			foreach($broadcastextras as $bcextra) {
				if ($bcextra['broadcast_id']==$broadcast['id'] && !empty($bcextra)) {
					echo "<div class='broadcastextra'> - <strong>".$bcextra['extra'].":</strong> ".$bcextra['description'] ."</div>";
				}
			echo "</li>";
			}
		}
	}
	else {
		echo "<p>No broadcasts</p>";
	}

echo "
	</div>
	<div class='plugin_description'>
	<img class='alignnone size-full wp-image-1131' src='http://www.awareframework.com/wp-content/uploads/2014/02/aware-providers.png' alt='aware-providers' width='87' height='110'>";

	if($tables==NULL) {
		echo "<p>No context providers</p></div>";
	}

	else if (!empty($tables)) {
		foreach ($tables as $table) {
			if(!empty($table)) {
				echo "<div class='provider'>
				";
				if(!empty($table['table_name'])) {echo "<h2>".$table['table_name']."</h2>";}
				echo "
				<p>".$table['desc']."</p>
				<div class='uriblock'><blockquote><strong>Content URI:</strong><br/><i>".$table['context_uri']."</i></blockquote></div>";
				echo "<div class='uritable'> <table cellspacing='5' cellpadding='5' class='providertable'>
					<thead>
					<tr class='provider'>
						<th class='provider_field'>Table field</th>
						<th class='provider_type'>Field type</th>
						<th class='provider_desc'>Description</th>
					</tr>
					</thead><tbody>";
				foreach ($tablefields as $tablefield) {
					
					if($tablefield['table_id']== $table['id'] && !empty($tablefield)) {
							echo "<tr class='provider'><td class='provider_field'>".$tablefield['column_name']."</td>
							<td class='provider_type'>".$tablefield['type']."</td>
							<td style='word-break' class='provider_desc'>".$tablefield['description']."</td>
							</td>
						";
					}
				}
			}
			echo "</tbody></table></div></div>";
		}
	}

//leave this echo till the end to fix the floats.	
echo"<div style='clear: both;'></div>
	";

?>