<?php 
		echo form_open('webservice/publish');
		
		echo "<p>Device</p>";
		echo form_dropdown('device', array('5934d7bc-ee6b-41ed-8b6a-d4a86f8f2a56'), '5934d7bc-ee6b-41ed-8b6a-d4a86f8f2a56');
		
		echo "<p>MQTT ID</p>";
		echo form_input('mqtt_id', '-850450903');
		
		echo "<p>ESM type</p>";
		$esm_types = array(
					1 => 'Free text',
					2 => 'Radio',
					3 => 'Checkbox',
					4 => 'Likert',
					5 => 'Quick answer',
					);
					
		echo form_dropdown('esm_type', $esm_types, '1');
		
		echo "<p>Title</p>";
		echo form_input('title', '');
		
		echo "<p>Instructions</p>";
		echo form_input('instructions', '');
		
		echo "<p>Submit button</p>";
		echo form_input('submit', '');
		
		echo "<p>Expiration</p>";
		echo form_input('expiration', '');
		
		echo "<p>Trigger</p>";
		echo form_input('trigger', '');
		
		echo "<p>Send</p>";
		echo form_submit('send', 'Send ESM!');
		
		
		
		
		echo form_close();
?>