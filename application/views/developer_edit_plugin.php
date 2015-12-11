<?php

 $add_img_properties = array(
          'src' => base_url().'application/views/images/developer/add.png',
          'title' => 'Add new',
          'class' => 'add_image',
          //'width' => '45',
          'height' => '24'
);

$remove_img_properties = array(
		  'src' => base_url().'application/views/images/delete_icon.png',
          'title' => 'Remove',
          'class' => 'remove_image',
          'height' => '12'
);

$addextra_img_properties = array(
          'src' => base_url().'application/views/images/developer/new_extra.png',
          'title' => 'Add a new extra to a broadcast',
          'class' => 'addextra_image',
          //'width' => '64',
          'height' => '24'
);

if (!isset($plugin_data['title'])) {
	echo '<h1>Create new plugin</h1>';
}
else {
	echo '<h1>Edit plugin</h1>';
}
?>
<?php
if ($this->uri->segment(2) == 'new_plugin') {
	$url = site_url('developer/new_plugin/success/0');
}
else if ($this->uri->segment(2) == 'edit_plugin') {
	$url = site_url('developer/edit_plugin/success/'.$plugin_data['id']);
}
/* echo '<form method="post" action="'.$url.'">'; */
echo form_open_multipart($url);

?>
<div class="edit-plugin-titles">Plugin name</div>
<div class="edit-plugin-values">
<?php
if (!isset($plugin_data['title'])) {
	echo '<input id="plugin_name" class="plugin_name" type="text" maxlength="100" name="plugin_name">';
	}
else {
	echo $plugin_data['title'];
}
?>

</div>
<?php
//the status is only visible to manager and when editing
if ($manager && !($create_new == 1) ){
$statusarr = array("0"=>"Pending","1"=>"Accepted","-1"=>"Declined");
echo "
	<div class='edit-plugin-titles'>Status</div>
	<div class='edit-plugin-values'>
		<select name='plugin_status' id='plugin-status'>";
			foreach($statusarr as $code => $status) {
				if (array_key_exists("status", $plugin_data) && $plugin_data["status"] == $code) {
					echo "<option value='".$code."' selected='selected'>".$status."</option>";
				} else {
					 echo "<option value='".$code."'>".$status."</option>";
				}
			}
echo	"</select>
	</div>
";
}
?>
<div class="edit-plugin-titles">Type</div>
<div class="edit-plugin-values">
<select name="plugin_type" id="plugin-type">
<?php
	$types = array("0"=>"Plugin","1"=>"Sensor");
	foreach ($types as $code => $type) {
		if ($code == $plugin_data['type']) {
			echo "<option value='".$code."' selected='selected'>".$type."</option>";
		}
		else echo "<option value='".$code."'>".$type."</option>";
	}
?>	

</select>
</div>
<div class="edit-plugin-titles">File</div>
<div id="fileupload" class="edit-plugin-values">
    <div id="fileuploadbtn" class="btn">
        <span>Upload package</span>
        <input type="file" name="plugin_package" id="uploadBtn" class="upload-button" />
    </div>
    <div id="filename" >
	<?php 
	/*if (isset($plugin_data['package_name']) && strpos($plugin_data['package_name'], ">") !== false) $package = "/var/www/html" . $plugin_data['package_name'];
	else $package="";
	
	 value="<?php echo $package; ?>"
	*/
	$package = null;
	?>
		<input id="uploadFile" placeholder="You haven't chosen a new package file yet" disabled="disabled"/>
		<input id="uploadFile2" name="upload_file_text" hidden value="not_removed"/>
	</div>
	<?php
		if ($package!="") {
			echo '<a id="remove-packagefile" >'. img($remove_img_properties) .'</a>';
		}
	?>
</div>

<div class="edit-plugin-titles">Git Repository URL</div>
<div class="edit-plugin-values">
	<?php
	echo '<input id="plugin_repository" class="plugin_repository" type="text" maxlength="80" name="plugin_repository"';
	if (isset($plugin_data["repository"])) {echo ' value = "' . $plugin_data['repository'] . '"';}
	echo '></input>';
?>
</div>
<div class="edit-plugin-titles">Icon</div>
<div id="iconupload" class="edit-plugin-values">
	<div id="uploadiconbtn" class="btn">
		<span>Upload icon</span>
		<input type="file" name="plugin_icon" id="iconuploadBtn" class="upload-button"/>
	</div>
	<div id="iconfilename" >
	<?php
	/*if (isset($plugin_data['iconpath'])) $iconpath = "/var/www/html" . $plugin_data['iconpath'];
	else $iconpath="";
	
	 value="<?php echo $iconpath; ?>" 
	*/
	$iconpath = null;
	?>
		<input id="uploadIconFile" type="text" name="upload_icon_text" placeholder="You haven't chosen a new icon file yet" disabled="disabled"/>
		<input id="uploadIconFile2" name="upload_icon_text" hidden value="not_removed"/>
	</div>
	<?php
		if ($iconpath!="") {
			echo '<a id="remove-iconfile" >'. img($remove_img_properties) .'</a>';
		}
	?>
</div>

<div class="edit-plugin-titles">Description</div>
<div class="edit-plugin-values">
<textarea id="plugin_description" class="plugin_desc_text" maxlength="400" name="plugin_description">
<?php
if (isset($plugin_data)) {
	echo $plugin_data['desc'];
}
?>
</textarea>
</div>
<div class="edit-plugin-titles">Settings</div>
<div class="edit-plugin-values">
<div class="settings">

<?php

	// The format <id>:<input field type> in the name-attribute of each text-field keeps track of the database id of each
	// entry for saving the changes into the database. 
	// Hashtag is simply used for each name-field to be easily parsed for its type and id in the database.
	
	// New (created with the UI) rows contain a new<running number>: identification before the hashtag to create insert-statements
	// for these rows only. The id is irrelevant here since its automatically generated with the insert statement but
	// the running number is still needed to find all the values with the controllers post()-function instead of
	// just the last value.

	if (!empty($settings)) {
		foreach($settings as $setting) {
			echo '<div id="setting'.$setting['id'].'" class="setting">'.
				'<a id="remove-settings" class="remove-button">' . img($remove_img_properties) . '</a>'.
				'<select class="plugin_settings_type" name="'.$setting['id'].':plugin_setting_type">'.
					'<option value="boolean"' . ($setting["setting_type"] == "boolean" ? " selected" : "" ).'>Boolean</option>'.
					'<option value="integer"' . ($setting["setting_type"] == "integer" ? " selected" : "" ).'>Integer</option>'.
					'<option value="real"' . ($setting["setting_type"] == "real" ? " selected" : "" ).'>Real</option>'.
					'<option value="text"' . ($setting["setting_type"] == "text" ? " selected" : "" ).'>Text</option>'.
				'</select>'.
				'<input class="plugin_settings" type="text" maxlength="400" name="'.$setting['id'].':plugin_setting" value="' . $setting['setting'] . '" placeholder="setting">'.
				'<input class="plugin_settings_desc" type="text" maxlength="400" name="'.$setting['id'].':plugin_setting_desc" value="' . $setting['desc'] . '" placeholder="description">'.
				'</div>';
		}	
		echo '<div class="add-setting-button" >'.
			'<a id="add-setting">' . img($add_img_properties) . '</a>'.
			'</div>';
	}
	else {
		echo '<div id="new_setting0" class="new_setting">'.
			'<a id="remove-settings" class="remove-button">' . img($remove_img_properties) . '</a>'.
			'<select class="plugin_settings_type" name="newempty:plugin_setting_type">'.
				'<option value="boolean">Boolean</option>'.
				'<option value="integer">Integer</option>'.
				'<option value="real">Real</option>'.
				'<option value="text">Text</option>'.
			'</select>'.
			'<input class="plugin_settings" type="text" maxlength="400" name="newempty:plugin_setting" placeholder="setting">'.
			'<input class="plugin_settings_desc" type="text" maxlength="400" name="newempty:plugin_setting_desc" placeholder="description">'.
			'</div>'.
			'<div class="add-setting-button" >'.
			'<a id="add-setting">' . img($add_img_properties) . '</a>'.
			'</div>';
	}
?>
</div>
</div>
<div class="edit-plugin-titles">Broadcasts</div>
<div id="broadcasts" class="edit-plugin-values">
<?php
	if (!empty($broadcasts)) {
		$i = 0;
		foreach($broadcasts as $broadcast) {
			echo '<div id="broadcast'.$broadcast['id'].'" class="broadcast">'.
				'<a id="remove-broadcast" class="remove-button">' . img($remove_img_properties) . '</a>'.
				'<input class="plugin_broadcast" type="text" maxlength="400" name="'.$broadcast['id'].':plugin_broadcast" placeholder="broadcast" value="' . $broadcast['broadcast'] . '">'.
				'<input class="plugin_broadcast_desc" type="text" maxlength="400" name="'.$broadcast['id'].':plugin_broadcast_desc" placeholder="description" value="' . $broadcast['desc'] . '">'.
				'<div class="add-newextra-button"><a class="add-newextra">' . img($addextra_img_properties) . '</a></div>';	
			$i++;
			if (!empty($broadcastextras)) {
				foreach($broadcastextras as $extra) {
					if ($extra['broadcast_id'] == $broadcast['id']) {
						echo '<div id="broadcastextra'.$extra['id'].'" class="broadcastextras">'. 
							'<a id="remove-broadcastextra" class="remove-button">' . img($remove_img_properties) . '</a>'.
							'<input class="plugin_extras" type="text" maxlength="400" name="'.$extra['id'].':plugin_broadcastextra:'.$broadcast['id'].'" placeholder="broadcast extra" value="' . $extra['extra'] . '">'.
							'<input class="plugin_extras_desc" type="text" maxlength="400" name="'.$extra['id'].':plugin_broadcastextra_desc:'.$broadcast['id'].'" placeholder="description" value="' . $extra['description'] . '">'.
							'</div>';
					}
				}
				
			}
			echo '</div>';
			if ($i == count($broadcasts)) { //if this is the last occurrence, add the add-button
				echo '<div class="add-broadcast-button" >'.
					'<a class="add-broadcast">' . img($add_img_properties) . '</a>'.
					'</div>';
			}
	}
}
else {
	echo '<div id="new_broadcast0" class="new_broadcast">'.
		'<a id="remove-broadcast" class="remove-button">' . img($remove_img_properties) . '</a>'.
		'<input class="plugin_broadcast" type="text" maxlength="400" name="new0:plugin_broadcast" placeholder="broadcast">'.
		'<input class="plugin_broadcast_desc" type="text" maxlength="400" name="new0:plugin_broadcast_desc" placeholder="description">'.
		'<div class="add-newextra-button"><a class="add-newextra">' . img($addextra_img_properties) . '</a></div>'.
		'</div>'.
		'<div class="add-broadcast-button" ><a class="add-broadcast">' . img($add_img_properties) . '</a></div>';
}
echo '</div>
<div class="edit-plugin-titles">Tables</div>
<div id="context_providers" class="edit-plugin-values">';

if (isset($tables)) {
	foreach($tables as $table) {
		echo '<div id="context_provider-'.$table['id'].'" class="context_provider" >'.
			'<a id="remove-contextprovider" class="remove-button">' . img($remove_img_properties) . '</a>'.
			'<input class="context_providers" type="text" maxlength="400" name="'.$table['id'].':context_providers" placeholder="table name" value="' . $table['table_name'] . '">'.
			'<input class="context_providers_uri" type="text" maxlength="100" name="'.$table['id'].':context_providers_uri" placeholder="URI" value="' . $table['context_uri'] . '">'.
			'<input class="context_provider_desc" type="text" maxlength="400" name="'.$table['id'].':context_provider_desc" placeholder="description" value="' . $table['desc'] . '">'.
			'</div>';
	}
	echo '<div class="add-context-button" ><a class="add-context">' . img($add_img_properties) . '</a></div>';
}
else {
	echo '<div class="add-context-button" >'.
		'<a class="add-context">'.img($add_img_properties).'</a>'.
		'</div>';
}
?>
</div>
<div class="edit-plugin-titles">Tablefields</div>
<div id="tables" class="edit-plugin-values">
<?php
	if (isset($tables)) {
	$emptytablecount=0;
	$typearray = array("NULL","INTEGER","REAL","TEXT","BLOB");
	
	foreach($tables as $table) {
		echo '<div id="table-'.$table['id'].'" class="table">';
		echo '<div id="table'.$table['id'].'" class="table_name">'.$table['table_name'].'</div>';
		$foundfields = false;
		if (isset($tablefields)) {
			$typeselect = '<div id="tableselect"><select class="table_type" ';
			foreach ($tablefields as $field) {
				if($field['table_id'] == $table['id']) {
					$foundfields = true;
					/*echo '<div id="tablefield-'.$field['field_id'].'" class="tablefields">'.
						'<a id="remove-tablefield" class="remove-button">' . img($remove_img_properties) . '</a>'.
						'<input class="table_name" type="text" maxlength="40" name="'.$field['field_id'].':table_name:'.$table['id'].'" placeholder="name" value="' . $field['column_name'] . '">'.
						'<input class="table_type" type="text" maxlength="16" size="10" name="'.$field['field_id'].':table_type:'.$table['id'].'" placeholder="type" value="' . $field['type'] . '">'.
						'<input class="table_desc" type="text" maxlength="400" size="50" name="'.$field['field_id'].':table_desc:'.$table['id'].'" placeholder="description" value="' . $field['description'] . '">'.
						'</div>';*/
					$typeselect = $typeselect . 'name="'.$field['field_id'].':table_type:'.$table['id'].'"> ';
					foreach ($typearray as $type) {
						if ($field['type'] == $type) $typeselect = $typeselect . '<option value="'.$type.'" selected="selected">'.$type.'</option> ';
						else $typeselect = $typeselect . '<option value="'.$type.'">'.$type.'</option> ';
					}
					$typeselect = $typeselect . '</select></div>';
				//'<div id="tableselect"><select class="table_type" name="'.$field['field_id'].':table_type:'.$table['id'].'"><option value="NULL">NULL</option><option value="INTEGER">INTEGER</option><option value="REAL">REAL</option><option value="TEXT">TEXT</option><option value="BLOB">BLOB</option></select></div>'.
					echo '<div id="tablefield-'.$field['field_id'].'" class="tablefields">'.
						'<a id="remove-tablefield" class="remove-button">' . img($remove_img_properties) . '</a>'.
						'<input class="table_name" type="text" maxlength="40" name="'.$field['field_id'].':table_name:'.$table['id'].'" placeholder="name" value="' . $field['column_name'] . '">'.
						$typeselect .
						'<input class="table_desc" type="text" maxlength="400" size="50" name="'.$field['field_id'].':table_desc:'.$table['id'].'" placeholder="description" value="' . $field['description'] . '">'.
						'</div>';
				}
				$typeselect = '<div id="tableselect"><select class="table_type" ';
			}
			
			if(!$foundfields) {
				echo '<div id="tablefield-'.$emptytablecount.'" class="tablefields">'.
					'<a id="remove-tablefield" class="remove-button">' . img($remove_img_properties) . '</a>'.
					'<input class="table_name" type="text" maxlength="40" name="new'.$emptytablecount.':table_name:'.$table['id'].'" placeholder="name">'.
					'<div id="tableselect"><select class="table_type" name="new'.$emptytablecount.':table_type:'.$table['id'].'"><option value="NULL">NULL</option><option value="INTEGER">INTEGER</option><option value="REAL">REAL</option><option value="TEXT">TEXT</option><option value="BLOB">BLOB</option></select></div>'.
					'<input class="table_desc" type="text" maxlength="400" size="50" name="new'.$emptytablecount.':table_desc:'.$table['id'].'" placeholder="description">'.
					'</div>';
				$emptytablecount++;
			}
			echo '<div class="add-table-button"><a class="add-table">' . img($add_img_properties) . '</a></div>';
		}
		echo '</div>';
	}
}
else {
	echo '<div class="tables-not-found">'.
		'Add tables to modify table information.'.
		'</div>';
}
?>
</div>
<div id="submitbuttons" >
	<div id="submitbutton"><input class="edit-plugin-submit" type="submit" value="Save changes" name="submit"></div>
	<?php
	if ($this->uri->segment(3) == 'new') $redirecturl = site_url('developer');
	else $redirecturl = site_url('developer/plugin') . '/' . $plugin_data['id'];
	?>
	<div id="reload"><a href="<?php echo $redirecturl; ?>" id="reloadbutton" >Cancel changes</a></div>
</div>

</form>

<div id="dialog" title="Basic dialog" style="display:none" >
  <p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the 'x' icon.</p>
</div>