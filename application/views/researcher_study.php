<?php

$datestring = "%d %F %Y";
$errors = $this->session->flashdata('errors');

$delete_img_properties = array(
          'src' => base_url(). '/application/views/images/delete_icon.png',
          'title' => 'Remove co-researcher from study',
          'class' => 'del_image',
          'width' => '11',
          'height' => '11'
);
$delete_study_img_properties = array(
          'src' => base_url().'/application/views/images/delete_icon.png',
          'title' => 'Remove study and data collected',
          'class' => 'delete-study-img',
          'width' => '11',
          'height' => '11'
);
$cancel_desc_edit_img_properties = array(
          'src' => base_url().'/application/views/images/delete_icon.png',
          'title' => 'Revert changes',
          'class' => 'del_image',
          'width' => '11',
          'height' => '11'
);
$ok_img_properties = array(
          'src' => base_url().'/application/views/images/ok.png',
          'title' => 'Confirm changes',
          'class' => 'ok_image',
          'width' => '13',
          'height' => '13'
);
$edit_img_properties = array(
          'src' => base_url().'/application/views/images/edit.png',
          'title' => 'Edit',
          'class' => 'edit_image',
          'width' => '11',
          'height' => '11'
);

$join_url = base_url() . 'index.php/webservice/index/' . $study_data["id"] . '/' . $study_data["api_key"];

$qr_img_properties = array(
          'src' => 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . $join_url . '&choe=UTF-8',
		  'id' => 'qr_img',
          'alt' => 'Study QRcode',
		  'title' => 'Study QRcode',
          'class' => 'qrcode',
          'width' => '300',
          'height' => '300'
);

// Flashdata variables, this is FIX for PHP v5.3
$fd_esm_freetext = $this->session->flashdata('esm-freetext');
$fd_esm_radio = $this->session->flashdata('esm-radio');
$fd_esm_checkbox = $this->session->flashdata('esm-checkbox');
$fd_esm_likert = $this->session->flashdata('esm-likert');
$fd_esm_quickanswer = $this->session->flashdata('esm-quickanswer');
$fd_esm_scale = $this->session->flashdata('esm-scale');

if (!$connected) {
	echo	'<div class="warning"><b>Warning: </b>Study database connection failed. Please contact administrator (aware@comag.oulu.fi).</div>';
}


echo'<div id="' . $study_data["id"]. '" class="study_id" "style="display: none;"></div>
	<div class="name">' . $study_data["study_name"];
if ($this->session->userdata('id') == $study_data['creator_id']) {
	echo 	'<a href="#" id="delete-study">' . img($delete_study_img_properties) . '</a></div>
			<div id="delete-study-dialog" title="Confirmation required">
				Are you sure you want to delete this study? <br>All of the collected data will be lost.
			</div>';
} else {
	echo '</div>';
}
echo '<div class="study-titles">Status:</div>';
	echo '<input type="text" style="display: none;" id="study-status" value="' . $study_data['status'] . '">';
	if ($this->session->userdata('id') == $study_data['creator_id'] || $this->session->userdata('manager') == 1) {
		echo	'<div class="study-values"><div class="switch-container"><span class="toggle-status', $study_data["status"] == 0 ? " active" : "", '">Closed</span><input type="checkbox" class="study-status switchbutton" id="close-confirm"', $study_data["status"] == 1 ? " checked" : "", '><label for="close-confirm"></label><span class="toggle-status', $study_data["status"] == 1 ? " active" : "", '">Open</span></div></div>
				<div id="close-study-dialog" title="Confirmation required">
					Are you sure you want to close this study? <br>No more data can be sent to this study after closing.
				</div>';
	} else {
		echo '<div class="study-values">';
		echo $study_data["status"] == 1 ? "Open" : "Closed";
		echo '</div>';
	}
	echo '
	<div class="study-titles">Join study:</div>
	<div class="study-description"><p>'
	. $join_url .
	'</p><a href="#" class="show_qr">Show QRcode</a>
			<div class="qr_dialog" title="QRcode"><a href="#" id="submit_qr">'
				. img($qr_img_properties) . 
			'</a></div>
	</div><div id="qr_form_hider" style="display:none">';
	echo form_open('researcher/download_qrcode', array('id' => 'qr_form'));
	echo form_input('join_url', $join_url);
	echo form_close();
	echo '</div><div class="study-titles">Description:</div>
	<div class="study-description">
	<textarea disabled rows="1" cols="60" id="expand_area">' . strip_tags($study_data["description"]) . '</textarea>
	</div>
	<div id="description-buttons">
		<div class="edit-button"><a href="#" class="edit-description">' . img($edit_img_properties) . '</a></div>
		<div class="save-cancel">
			<div class="cancel-button">
				<a href="#" class="delete-co">' . img($cancel_desc_edit_img_properties) . '</a>
			</div>
			<div class="save-button">
				<a href="#" class="delete-co">' . img($ok_img_properties) . '</a>
			</div>
		</div>
	</div>
	<div class="study-titles">Sensors:</div>
	<div class="study-description">';
	echo	"<div id='sensors-settings-wrapper'>
				<ul id='sensors-settings'>";
	
	$type = "";
	foreach ($sensors_configurations as $configuration) {
		if ($configuration["plugin_name"] != $type) {
			if ($type != "") {
				echo "</ul>";
			}
			
			echo	"<li class='sensor'>" . 
					"<div class='sensor-arrow up'></div>".
					"<input type='hidden' class='package-name' value='" . $configuration["package_name"] . "'>".
					"<p class='" . $configuration["type"] . " label'>" . $configuration["plugin_name"] . "</p>".
					"<ul class='sensor-settings'>";
			$type = $configuration["plugin_name"];
		}
		echo	"<li class='sensor-setting'>";
		// Sensor setting type boolean, use checkbox
		if ($configuration["setting_type"] == "boolean") {
			// If sensor was enabled previously (flashdata), check checkbox

			echo 	"<input type='checkbox' id='" . $configuration["setting_name"] . "' class='sensor value boolean basic' name='" . $configuration["setting_name"] . "'>".
					"<label for='" . $configuration["setting_name"] . "'>" . ucfirst(str_replace("_", " ", $configuration["setting_name"])) . "</label>";
		// Sesor setting type int/text, use normal input fied
		} else if (($configuration["setting_type"] == "integer" || $configuration["setting_type"] == "text" || $configuration["setting_type"] == "real")) {
			// ESM Questionnaire plugin
			echo 	"<label" . ($configuration["plugin_id"] == 67 ? " style='display: none !important;'" : '') ." for='" . $configuration["setting_name"] . "' class='setting-label'>" . ucfirst(str_replace("_", " ", $configuration["setting_name"])) . ":</label>".
					"<input" . ($configuration["plugin_id"] == 67 ? " style='display: none !important;'" : '') ." type='text' id='" . $configuration["setting_name"] . "' name='" . $configuration["setting_name"] . "' class='sensor value " . $configuration["setting_type"] . "' placeholder='" . $configuration["setting_default_value"] . "' value=''>";
		}
		// Setting help / tooltip (don't display for ESM questionnaire because the setting itself is hidden)
		if ($configuration["plugin_id"] != "67") {
			echo	"<p class='sensor-setting-description'>" . ucfirst($configuration["setting_description"]) . "</p>".
					"<input type='hidden' class='sensor-setting-name' value='" . $configuration["setting_name"] . "'>";
		}

		echo "</li>";

	}

	echo	"</ul></ul></div></div>";

	echo	'	<div id="sensors-buttons">
					<div class="edit-button"><a href="#" class="edit-description">' . img($edit_img_properties) . '</a></div>
					<div class="save-cancel">
						<div class="cancel-button">
							<a href="#" class="delete-co">' . img($cancel_desc_edit_img_properties) . '</a>
						</div>
						<div class="save-button">
							<a href="#" class="delete-co">' . img($ok_img_properties) . '</a>
						</div>
					</div>
				</div>

	<div class="study-titles">Owner:</div>
	<div class="study-values"><p>' . $study_data["last_name"] . ', ' . $study_data["first_name"] . '</p></div>
	<div class="study-titles">Co-researchers:</div>
	<div class="co-researchers">';

	foreach($co_researchers as $co) {
		echo '
			<div id="' . $co["id"] . '" class="co-name">' .
			$co["last_name"] . ', ' . $co["first_name"] .
			'<a href="#" class="delete-co">' . img($delete_img_properties) . '</a>
			<div class="delete-dialog">Are you sure you want to delete co-researcher ' .
			$co["last_name"] . ', ' . $co["first_name"] .
			'?</div></div>';
	}
	
	echo 
	'<div class="add-co">
		<a href="#" class="add-co">Add co-researcher</a>
		<div class="add-dialog">
			Please enter email address:<br>
			<input type="text" id="email" size="23"><br>
			<span class="dialog-error" style="display:none;" >Invalid email!</span>
		</div>
	</div>';
	
	echo 
	'</div>
	<div class="study-titles">Database name:</div>
	<div class="study-values"><p>' . $study_data["db_name"] . '</p></div>';
	
	echo
	'<div class="study-titles">Database access:</div>
	<div class="study-values"><a href="#" id="show-db-credentials">View credentials</a></p></div>

	<div id="study-data-dialog" title="View credentials">
		<p>To view the collected data, use any MySQL client with the following database credentials:</p>
		<b>Hostname: </b>' . (($db_credentials["db_hostname"] == "localhost") ? "localhost" : $db_credentials["db_hostname"]).'<br>
		<b>Port: </b>' . $db_credentials["db_port"] . '<br>
		<b>Username: </b>' . $db_credentials["db_username"] . '<br>
		<b>Password: </b>' . $db_credentials["db_password"] . '<br>
	</div>';
		
	echo
	'<div class="study-titles">Created:</div>
	<div class="study-values" id="study-created-value"><p>' . mdate($datestring, $study_data["created"]) . ' </p></div>
		<span id="creation-date">' . $study_data["created"] . '</span>
	<div class="study-titles">API key:</div>
	<div class="study-values"><p>' . $study_data["api_key"] . '</p></div>
	<div class="study-titles">Visualization:</div>
	<div class="study-values">';
	
	if (count($data_collected) == 0) {
		echo'<div class="no-data">There\'s no data collected for this study.</div></div>';
	} else {
	?>
		
		<div style="float:right">
			<form action="/index.php/visualizations/study/<?php echo $study_data["id"] ?>">
		    	<input type="submit" value="Create visualizations" style="background-color:#33B5E5; box-shadow:rgba(0,0,0,0.5) 0px 0px 6px; font-size: 14px; color:#FFFFFF; border:none; height:40px;">
		    </form>
		</div>
		
	<?php
		echo'
		<div id="visualization-data">

		<div id="visualization-options">
		
			<p class="title" id="visualization-date-title">Date: </p>
			
			<div id="datepicker-wrapper">
				<div id="datepicker"></div>
				<input type="hidden" name="date" id="selectedDate" value="">
			</div>
		</div>
		
		<table id="study-data" class="tablesorter">
			<thead> 
			<tr class="table-title">
				<td class="study-data-type">Type</td>
				<td class="study-data-records">Total records</td>
			</tr>
			</thead> ';
			
			foreach($data_collected as $dc) {
				echo '
					<tr class="plugin-data">
						<td class="plugin-data-type">' . $dc['TABLE_NAME'] . '</td>
						<td class="plugin-data-value">' . $dc['TABLE_ROWS'] . '</td>
					</tr>';
			}
			
			echo 
			'</table>

			
			</div>

			<p class="title">Devices:</p>
			<div id="visualization-devices">
				<div id="visualization-loader" class="ajax-loader"></div>
			</div></div>';
	}
	echo	'<div class="study-titles">Devices:</div>
			<div class="study-values" id="devices-values">';
	
	if (!$study_devices) {
		echo '<div class="no-data">There\'s no devices linked to this study.</div></div>';
	} else {
		echo 
		'<input class="search" id="devices-search" placeholder="Search devices"><div id="devices-loader" class="ajax-loader"></div>
		<div class="table-navigation">
		</div>
		<table id="study-devices">
			<thead> 
			<tr class="title">
				<td class="device-select"><input type="checkbox" id="select-all" value="1" class="basic"><label for="select-all" class="no_top_margin">Select all</label></td>
				<td class="device-id"><img src="'.base_url().'/application/views/images/ASC.png" height="10" width="10" style="margin-right:5px;" class="sort_arrow">Device ID</td>
				<td class="device-label">Label</td>
			</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<div class="table-navigation">
		</div>

	
		</div>
			<div class="study-titles">Send to device(s):</div>
			<div class="study-values">
			<div class="tabs">
				  <ul>  
					<li>
						<a id="tab-esm" href="#esm">ESM</a>  
					</li>  
					<li>
						<a id="tab-broadcasts" href="#broadcasts">Broadcasts</a>  
					</li>  
					<li>
						<a id="tab-configuration" href="#configuration">Configure</a>  
					</li>
					<li>
						<a id="tab-custom" href="#custom">Custom</a>  
					</li>
				  </ul>
			</div>
	
			<div id="esm" class="tab-wrapper">
			
				<div class="error-state">';
				if ($this->session->flashdata('error_state')){
					echo $this->session->flashdata('error_state');
				}
				
		echo	'</div>

				<div class="tab-content title">Message type:</div>
				<div class="tab-content value">';
				
					$esm_types = array(
									1 => 'Free text',
									2 => 'Radio',
									3 => 'Checkbox',
									4 => 'Likert',
									5 => 'Quick answer',
									6 => 'Scale'
								);
					$esm_selected = $this->session->flashdata('esm-type') ? $this->session->flashdata('esm-type') : 1;
									
					//echo form_dropdown('esm-type', $esm_types, '1', 'id="esm-type"');
					echo form_dropdown('esm-type', $esm_types, $esm_selected, 'id="esm-type"');
		
		// ESM thresholds, same for every ESM message
		$esm_threshold = array(
				0 => 'Unlimited',
				10 => '10 seconds',
				30 => '30 seconds',
				60 => '1 minute',
				120 => '2 minutes',
				180 => '3 minutes'
			);
					
		// Option #1: Free text			
		echo'
				</div>

				<div id="esm-messages">

					<div class="esm-message 1">';
					
						$form_attr = array(
									"id" => "esm-1",
									"class" => "esm-form",
								);
			
		echo	 form_open("webservice/publish/esm/1", $form_attr) . '

						<div class="tab-content title">Title:</div>
						<div class="tab-content value">' . form_input('esm-title', $fd_esm_freetext['esm-title'], 'placeholder="ESM Freetext"');

						if (isset($errors['esm-title'])) {
							echo '<span class="error-msg">*</span>';
						}
						
		echo			'</div>
						
						<div class="tab-content title">Instructions:</div>
						<div class="tab-content value">' . form_input('esm-instructions', $fd_esm_freetext['esm-instructions'], 'placeholder="The user can answer an open ended question"');
					
						if (isset($errors['esm-instructions'])) {
							echo '<span class="error-msg">*</span>';
						}
						
		echo			'</div>
						
						<div class="tab-content title">Time to answer:</div>';
		echo '							
						<div class="tab-content value">' . form_dropdown('esm-threshold', $esm_threshold, $fd_esm_freetext['esm-threshold'], 'class="esm-threshold"') . '</div>
						
						<input type="hidden" id="study-id" name="study-id" value="' . $study_data["id"] . '">
						<input type="hidden" name="mqtt-type" value="esm">
						<input type="hidden" name="mqtt-class" value="free-text">'.form_close().'</div>';
		
		// Option #2: Radio
		echo '		<div class="esm-message 2">';
		
						$form_attr = array(
									"id" => "esm-2",
									"class" => "esm-form",
								);
			
		echo 	form_open("webservice/publish/esm/2", $form_attr) . '

						<div class="tab-content title">Title:</div>
						<div class="tab-content value">' . form_input('esm-title', $fd_esm_radio['esm-title'], 'placeholder="ESM Radio"');
						
						if (isset($errors['esm-title'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						
						<div class="tab-content title">Instructions:</div>
						<div class="tab-content value">' . form_input('esm-instructions', $fd_esm_radio['esm-instructions'], 'placeholder="The user can only choose one option"');
						if (isset($errors['esm-instructions'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						
						<div class="tab-content title">Options:</div>

						<div class="tab-content value"><input type="text" class="esm-options" name="esm-options" style="width:300px" value="" data-placeholder="Options, divide by enter">';
						if (isset($errors['esm-options'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						<div class="tab-content title">Time to answer:</div>';
		echo '							
						<div class="tab-content value">' . form_dropdown('esm-threshold', $esm_threshold, $fd_esm_radio['esm-threshold'], 'class="esm-threshold"') . '</div>
						
						<input type="hidden" id="study-id" name="study-id" value="' . $study_data["id"] . '">
						<input type="hidden" name="mqtt-type" value="esm">
						<input type="hidden" name="mqtt-class" value="radio">'
						
						.form_close().'
						
					</div>';

		// Option #3: Checkbox
		echo '		<div class="esm-message 3">';
		
						$form_attr = array(
									"id" => "esm-3",
									"class" => "esm-form",
								);
			
		echo	 form_open("webservice/publish/esm/3", $form_attr) . '
					
						<div class="tab-content title">Title:</div>
						<div class="tab-content value">' . form_input('esm-title', $fd_esm_checkbox['esm-title'], 'placeholder="ESM Checkbox"');
						if (isset($errors['esm-title'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						
						<div class="tab-content title">Instructions:</div>
						<div class="tab-content value">' . form_input('esm-instructions', $fd_esm_checkbox['esm-instructions'], 'placeholder="The user can choose multiple options"');
						if (isset($errors['esm-instructions'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						
						<div class="tab-content title">Options:</div>

						<div class="tab-content value"><input type="text" class="esm-options" name="esm-options" style="width:300px" value="" data-placeholder="Options, divide by enter">';
						if (isset($errors['esm-options'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						<div class="tab-content title">Time to answer:</div>';
		echo '							
						<div class="tab-content value">' . form_dropdown('esm-threshold', $esm_threshold, $fd_esm_checkbox['esm-threshold'], 'class="esm-threshold"') . '</div>
						
						<input type="hidden" id="study-id" name="study-id" value="' . $study_data["id"] . '">
						<input type="hidden" name="mqtt-type" value="esm">
						<input type="hidden" name="mqtt-class" value="checkbox">'
						
						.form_close().'
						
					</div>';

		// Option #4: Likert scale
		echo '		<div class="esm-message 4">';
		
						$form_attr = array(
									"id" => "esm-4",
									"class" => "esm-form",
									);
			
		echo	 form_open("webservice/publish/esm/4", $form_attr) . '
					
						<div class="tab-content title">Title:</div>
						<div class="tab-content value">' . form_input('esm-title', $fd_esm_likert['esm-title'], 'placeholder="ESM Likert"');
						if (isset($errors['esm-title'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						
						<div class="tab-content title">Instructions:</div>
						<div class="tab-content value">' . form_input('esm-instructions', $fd_esm_likert['esm-instructions'], 'placeholder="User rating 1 to 5 or 7 at 0.5 or 1 step increments"');
						if (isset($errors['esm-instructions'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>';
						
						$likert_max = array(
										5 => '5',
										7 => '7',
									);
						
		echo '			<div class="tab-content title">Likert max:</div>
						<div class="tab-content value">' . form_dropdown('esm-likertmax', $likert_max, $fd_esm_likert['esm-likertmax'], 'id="esm-likertmax"') . '</div>
						
						<div class="tab-content title">Likert max label:</div>
						<div class="tab-content value">' . form_input('esm-likertmax-label', $fd_esm_likert['esm-likertmax-label'], 'placeholder="Great"');
						if (isset($errors['esm-likertmax-label'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>';
		
				echo'		<div class="tab-content title">Likert min label:</div>
						<div class="tab-content value">' . form_input('esm-likertmin-label', $fd_esm_likert['esm-likertmin-label'], 'placeholder="Bad"');
						if (isset($errors['esm-likertmin-label'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>';
        
                        $likert_step = array(
										0 => '0.5',
										1 => '1',
									);
        
        echo '			<div class="tab-content title">Likert step:</div>
						<div class="tab-content value">' . form_dropdown('esm-likert-step', $likert_step, $fd_esm_likert['esm-likert-step'], 'id="esm-likert-step" placeholder="1"') . '</div>';
				echo '<div class="tab-content title">Time to answer:</div>';
		echo '							
						<div class="tab-content value">' . form_dropdown('esm-threshold', $esm_threshold, $fd_esm_likert['esm-threshold'], 'class="esm-threshold"') . '</div>
						
						<input type="hidden" id="study-id" name="study-id" value="' . $study_data["id"] . '">
						<input type="hidden" name="mqtt-type" value="esm">
						<input type="hidden" name="mqtt-class" value="likert">'
						
						.form_close().'
						
					</div>';

		// Option #5: Quick answer
		echo '		<div class="esm-message 5">';
		
						$form_attr = array(
									"id" => "esm-5",
									"class" => "esm-form",
									);
			
		echo 	form_open("webservice/publish/esm/5", $form_attr) . '
					
						<div class="tab-content title">Title:</div>
						<div class="tab-content value">' . form_input('esm-title', $fd_esm_quickanswer['esm-title'], 'placeholder="ESM Quick Answer"');
						if (isset($errors['esm-title'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						
						<div class="tab-content title">Instructions:</div>
						<div class="tab-content value">' . form_input('esm-instructions', $fd_esm_quickanswer['esm-instructions'], 'placeholder="One touch answer"');
						if (isset($errors['esm-instructions'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						
						<div class="tab-content title">Options:</div>

						<div class="tab-content value"><input type="text" class="esm-options" name="esm-options" style="width:300px" value="" data-placeholder="Options, divide by enter">';
						if (isset($errors['esm-options'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						<div class="tab-content title">Time to answer:</div>';
		echo '							
						<div class="tab-content value">' . form_dropdown('esm-threshold', $esm_threshold, $fd_esm_quickanswer['esm-threshold'], 'class="esm-threshold"') . '</div>
						
						<input type="hidden" id="study-id" name="study-id" value="' . $study_data["id"] . '">
						<input type="hidden" name="mqtt-type" value="esm">
						<input type="hidden" name="mqtt-class" value="quick-answer">
						
					</div>';
		echo form_close();
					
					// Option #6 Scales
		echo '		<div class="esm-message 6">';
		
						$form_attr = array(
									"id" => "esm-6",
									"class" => "esm-form",
									);
			
		echo	 form_open("webservice/publish/esm/6", $form_attr) . '
					
						<div class="tab-content title">Title:</div>
						<div class="tab-content value">' . form_input('esm-title', $fd_esm_scale['esm-title'], 'placeholder="ESM Scale"');
        
						if (isset($errors['esm-title'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>
						
						<div class="tab-content title">Instructions:</div>
						<div class="tab-content value">' . form_input('esm-instructions', $fd_esm_scale['esm-instructions'], 'placeholder="User scale from X to Y at Z step increments"');
						if (isset($errors['esm-instructions'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>';
		
		echo '			<div class="tab-content title">Scale min:</div>
						<div class="tab-content value">' . form_input('esm-scale-min', $fd_esm_scale['esm-scale-min'], 'id="esm-scale-min"') . '</div>
						
						<div class="tab-content title">Scale min label:</div>
						<div class="tab-content value">' . form_input('esm-scale-min-label', $fd_esm_scale['esm-scale-min-label'], 'placeholder="Bad"');
						if (isset($errors['esm-scale-min-label'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>';
						
		echo '			<div class="tab-content title">Scale max:</div>
						<div class="tab-content value">' . form_input('esm-scale-max', $fd_esm_scale['esm-scale-max'], 'id="esm-scale-max"') . '</div>
						
						<div class="tab-content title">Scale max label:</div>
						<div class="tab-content value">' . form_input('esm-scale-max-label', $fd_esm_scale['esm-scale-max-label'], 'placeholder="Great"');
						if (isset($errors['esm-scale-max-label'])) {
							echo '<span class="error-msg">*</span>';
						}
		echo			'</div>';
        echo '			<div class="tab-content title">Scale start:</div>
						<div class="tab-content value">' . form_input('esm-scale-start', $fd_esm_scale['esm-scale-start'], 'id="esm-scale-start" placeholder="0"') . '</div>';
        echo '			<div class="tab-content title">Scale step:</div>
						<div class="tab-content value">' . form_input('esm-scale-step', $fd_esm_scale['esm-scale-step'], 'id="esm-scale-step" placeholder="1"') . '</div>';

		echo '			<div class="tab-content title">Time to answer:</div>				
						<div class="tab-content value">' . form_dropdown('esm-threshold', $esm_threshold, $fd_esm_scale['esm-threshold'], 'class="esm-threshold"') . '</div>
						
						<input type="hidden" id="study-id" name="study-id" value="' . $study_data["id"] . '">
						<input type="hidden" name="mqtt-type" value="esm">
						<input type="hidden" name="mqtt-class" value="scale">
						'. form_close(). '
					</div>';
        
				echo '	
					<div class="buttons">
						<input type="submit" name="send" value="Add to queue" id="add-to-queue" class="sendbutton2">
						<input type="submit" name="send" value="Send ESM(s)" class="sendbutton">
					</div>
					
					<div id="esm-queue-wrapper">
						<p><b>ESM Queue</b></p>
						<table id="esm-queue">
							<thead>
								<tr>
									<td class="esm-queue-message-type">Type</td>
									<td class="esm-queue-message-title">Title</td>
									<td class="esm-queue-message-remove"></td>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
					
				</div>

			</div>';
		
		echo
			'<div id="broadcasts" class="tab-wrapper">

				<div class="error-state">';
				if ($this->session->flashdata('error_state')){
					echo $this->session->flashdata('error_state');
				}
				
		echo	'</div>
			
				<div class="tab-content title">
					What kind of broadcasts you would like to send?
				</div>';
				
				$form_attr = array(
								"id" => "broadcasts",
								"class" => "broadcasts-form",
							);
				
		echo	form_open("#", $form_attr).'
				<div class="tab-content value">';
					$broadcasts_types = array(
									'ACTION_AWARE_SYNC_DATA' => 'Sync database',
									'ACTION_AWARE_CLEAR_DATA' => 'Clean database',
								);
					$broadcasts_selected = $this->session->flashdata('broadcasts-type') ? $this->session->flashdata('broadcasts-type') : 1;
									
					echo form_dropdown('broadcasts-type', $broadcasts_types, $broadcasts_selected, 'id="broadcasts-type"') . '
				</div>
				
				
				<div id="broadcasts-messages">

					<div class="broadcasts-message ACTION_AWARE_SYNC_DATA">
						<div class="tab-content title">
							<span style="color: #33B5E5;">This broadcasts message requests the selected devices to send their data immediately to the server.</span>
						</div>
						
						<div class="buttons">';
						

									
			echo		'<input type="hidden" id="study-id" name="study-id" value="' . $study_data["id"] . '">
						<input type="hidden" name="mqtt-type" value="broadcasts">'.
						form_submit('send', 'Send broadcasts', 'class="sendbutton"').
						form_close().'
						</div>
					</div>
					
					<div class="broadcasts-message ACTION_AWARE_CLEAR_DATA">
						<div class="tab-content title">
							<span style="color: #33B5E5;">Database entries for selected devices will be cleared. Proceed with caution!</span>
						</div>
						
						<div class="buttons">
						'.form_submit('send', 'Send broadcasts', 'class="sendbutton"')
						.form_close().'
						</div>
					</div>
					
				</div>
				
			</div>';
		echo
			'<div id="configuration" class="tab-wrapper">
			
				<div class="error-state">';
				if ($this->session->flashdata('error_state')){
					echo $this->session->flashdata('error_state');
				}
				
		echo	'</div>
		
				<div class="tab-content title">
					Configuration:
				</div>';
				
				$form_attr = array(
					"id" => "configuration",
					"class" => "configuration-form",
				);
				
		echo	form_open("#", $form_attr).'
			
				<div class="tab-content value">
					<input type="hidden" name="configuration" class="configuration" style="width:400px" placeholder="Insert your configuration here">';
				if (isset($errors['configuration'])) {
					echo '<span class="error-msg">' . $errors['configuration'] . '</span>';
				}
		echo	'</div>
				
				
				<div class="buttons">';
													
	echo		'<input type="hidden" id="study-id" name="study-id" value="' . $study_data["id"] . '">
				<input type="hidden" name="mqtt-type" value="configuration">'.
				form_submit('send', 'Send configuration', 'class="sendbutton"').
				form_close().'
				</div>
				
			</div>
			
			<div id="custom" class="tab-wrapper">

				<div class="error-state">';
				if ($this->session->flashdata('error_state')){
					echo $this->session->flashdata('error_state');
				}
				
		echo	'</div>';
		
				$form_attr = array(
					"id" => "custom",
					"class" => "custom-form",
				);
			
		echo	form_open("webservice/publish/custom/message", $form_attr). '
			
				<div class="tab-content title">Topic:</div>
				<div class="tab-content value">' . form_input('custom-topic', '', 'placeholder="Message topic"');
				if (isset($errors['custom-topic'])) {
					echo '<span class="error-msg">' . $errors['custom-topic'] . '</span>';
				}
				
	echo		'</div>
	
				<div class="tab-content title">Description:</div>
				<div class="tab-content value">' . form_input('custom-description', '', 'placeholder="Message description"');
				if (isset($errors['custom-description'])) {
					echo '<span class="error-msg">' . $errors['custom-description'] . '</span>';
				}
				
	echo		'</div>
	
				<div class="buttons">';
							
	echo		'<input type="hidden" id="study-id" name="study-id" value="' . $study_data["id"] . '">
				<input type="hidden" name="mqtt-type" value="custom">'.
				form_submit('send', 'Send message', 'class="sendbutton"').
				form_close().'
				</div>
				
			</div>
			
			</div>
		<div class="study-titles">MQTT history:</div>
			<div class="study-values">
				<div id="mqtt-history-wrapper">
					<input class="search" id="mqtt-history-search" placeholder="Search from MQTT history">
					<table id="mqtt-history" class="tablesorter">
						<thead>
							<tr>
								<td class="mqtt-history-date">Date</td>
								<td class="mqtt-history-topic">Topic</td>
								<td class="mqtt-history-title">Title</td>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
					
				
				<div class="more"><a href="#" class="more-link">Show more</a></div>
				<div class="less"><a href="#" class="less-link">Show less</a></div>
				<div id="pager-mqtt-history" class="pager">
					<form> 
					<select class="pagesize"> 
					  <option selected="selected" value="10">10</option> 
					  <option value="1000">all</option> 
					</select> 
				  </form> 
				</div>
			</div>
				
			</div>';
		} // else (if no devices) end here

	echo	'<div id="remove-device-dialog" title="Confirmation required">
				Are you sure you want to remove this device from the study? <br>All data collected by the device will be lost.
			</div>';
?>