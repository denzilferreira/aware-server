<?php
//print_r($chart_properties);
$count =  count($chart_data['chart_properties']);

//Echo existing chart data to the JavaScript. Maybe change it to JSON encode.
for ($i=0; $i < $count; $i++) {

	//Send chart related information to the client.
	echo '<div class="visualizations-container" style="display: none" 
	data-image="data:image/png;base64,'.$chart_data['chart_properties'][$i]['image'].'">';
	echo json_encode($chart_data['chart_properties'][$i]);
	echo '</div>';

}
//echo '<div style="display: none" class="id" >'. $study_data['id'] . '</div>';
?>

<div id="freewall" class="free-wall">
</div>