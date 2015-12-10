<?php
//print_r($chart_properties);
$count =  count($chart_data['chart_properties']);
//echo "count". $count;
//Echo existing chart data to the JavaScript. Maybe change it to JSON encode.
for ($i=0; $i < $count; $i++) {

	//Send chart related information to the client.
	echo '<div class="visualizations-container" style="display: none" 
	data-image="data:image/png;base64,'.$chart_data['chart_properties'][$i]['image'].'">';
	echo json_encode($chart_data['chart_properties'][$i]);
	echo '</div>';

}
//echo json_encode($chart_properties);
echo '<div style="display: none" class="id" >'. $study_data['id'] . '</div>';
echo '<form style="display: none" id="edit_chart" action=/index.php/visualizations/edit_chart/'. $study_data['id'] . '/ ></div>';
?>


<div style="margin-left: 0.5%; margin-bottom: 10px">
	Public page link: 
	<?php 
		echo  '<a href="'.base_url() . 'index.php/visualizations_public/index/' .$study_data['id'] .'">'.base_url() . 'index.php/visualizations_public/index/' .$study_data['id'] . '</a>';
	?>
</div>
<div id="freewall" class="free-wall">
</div>