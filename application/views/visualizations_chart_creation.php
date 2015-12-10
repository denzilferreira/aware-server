<?php
	//Boolean indicating new chart creation
	$new_chart = true;

	if(isset($tables))
	{
		$tables_count = count($tables);
		echo '<div style="display: none" class="visualizations-chart_creation-tables">';
		echo json_encode($tables);
		echo '</div>';
	}
	echo '<div style="display: none" class="id">'. $study_id . '</div>';
	/*echo "tables_count: ".$tables_count;
	echo '<div class="visualizations-chart_creation-container">';*/
	/*foreach ($tables as $table) {
		echo 'table_name-'.$table['TABLE_NAME']."\n";
	}*/
/*	echo '> 
	</div>';*/
	//var_dump($tables);
	if(isset($chart_params))
	{
		$new_chart = false;
		//Debug
		//echo 'chart_params\n';
		//print_r($chart_params);
		//echo '\nchart id:' . $chart_id;
		//Send information to client.
		echo '<div style="display: none" class="chart_params">' .
		json_encode($chart_params) .
		'</div>';
	}
	//var_dump($devices);
?>
<div class='chart_window'>
	<div class='nav_bar'>
		<ul id='nav_top'>
			<li class='active' ><a id='nav_chart' href="#chart_creation_window">1. Choose a chart</a></li>
			<li><a id='nav_data' href="">2. Data selection</a></li>
			<li><a id='nav_fine' href="">3. Fine tuning</a></li>
		</ul>
	</div>
	<div class='window_container'>
		<div id='chart_creation_window' class='content_window active'>
			<div class='choose_chart'>
				<table id='chart_table'>
				  <tr class='chart_table_header'>
					<th>Column chart</th>
					<th>Pie chart</th>
					<th>Histogram</th>
				  </tr>
				  <tr class='chart_table_img'>
					<td><img id='column' class="img_clicable"></img></td>
					<td><img id='pie' class="img_clicable"></img></td>
					<td><img id='histogram' class="img_clicable"></img></td>
				  </tr>
				  <tr class='chart_table_header'>
					<th>Scatter chart</th>
					<th>Line chart</th>
					<th>Box chart</th>
				  </tr>
				  <tr class='chart_table_img'>
					<td><img id='scatter' class="img_clicable"></img></td>
					<td><img id='line' class="img_clicable"></img></td>
					<td><img id='box' class="img_clicable"></img></td>
				  </tr>
				  
				</table>
			</div>
		</div>
		<div id='data_selection_column' class='content_window' style='display: none'>
			<div class='window_top'>
				<label class="ds_chart_text">Column chart</label>
				<label for="speed">Select a table : </label>
					    <select name="table_name" class="table_name">
					    	
					     	<?php for ($i=0; $i<$tables_count; $i++) { ?>
				
				    		<option class='test'><?php echo $tables[$i]['TABLE_NAME'] ?></option>
				     
							<?php } ?>
					    </select>
				<ul>
			
				</ul>
			</div>
			<div class='window_middle'>
				<div class='window_middle_left'>
					<ul>
						<li>
						<!-- 	<label for="y">Select y-axis variable : </label>
							    <select name="y" class="y">
							
							</select>
						</li>
						<li>
					<label for="calculations">Additional features : </label>
					    <select name="calculations" ´class="calculations">
					    	<option>Mean/median/avg/sum</option>
					      
					    </select></li> -->
					    <li>
					<label for="colour">Grouping variable : </label>
					    <select name="colour" class="colour">
					   
					    </select></li>
					    <li>
					<label for="fill">Stacking variable : </label>
					    <select name="fill" class="fill">
					    	
					    </select></li>
					</ul>
				</div>
				<div class='window_middle_right'>
					
				</div>
			</div>
			<div class='window_bottom'>
				<label for="x">Select x-axis variable : </label>
				    <select name="x" class="x">
				     
				    </select>
			</div>
		</div>
		<div id='data_selection_pie' class='content_window' style='display: none'>
			<div class='window_top'>
				<label class="ds_chart_text">Pie chart</label>
				<label for="speed">Select a table : </label>
					    <select name="table_name" class="table_name">
					    	
					     	<?php for ($i=0; $i<$tables_count; $i++) { ?>
				
				    		<option><?php echo $tables[$i]['TABLE_NAME'] ?></option>
				     
							<?php } ?>
					    </select>
				<ul>
			
				</ul>
			</div>
			<div class='window_middle'>
				<div class='window_middle_left'>
					<ul>
						<li>
							<label for="angle">Angle variable: </label>
							    <select name="angle" class="angle">
							
							</select>
						</li>
					<!-- 	<li>
					<label for="calculations">Additional features : </label>
					    <select name="calculations" ´class="calculations">
					    	<option>Mean/median/avg/sum</option>
					      
					    </select></li> -->
					 
					</ul>
				</div>
				<div class='window_middle_right'>
				</div>
			</div>
			<div class='window_bottom'>
				<!-- <label for="slicing">Slicing variable : </label>
				    <select name="slicing" class="slicing">
				   
				    </select> -->
			</div>
		</div>
		<div id='data_selection_histogram' class='content_window' style='display: none'>
			<div class='window_top'>
				<label class="ds_chart_text">Histogram</label>
				<label for="speed">Select a table : </label>
					    <select name="table_name" class="table_name">
					    	
					     	<?php for ($i=0; $i<$tables_count; $i++) { ?>
				
				    		<option><?php echo $tables[$i]['TABLE_NAME'] ?></option>
				     
							<?php } ?>
					    </select>
				<ul>
			
				</ul>
			</div>
			<div class='window_middle'>
				<div class='window_middle_left'>
				<!--	<ul>
						<li>
							<label for="y">Select y-axis variable : </label>
							    <select name="y" class="y">
							
							</select>
						</li>
						<li>
					<label for="calculations">Additional features : </label>
					    <select name="calculations" ´class="calculations">
					    	<option>Mean/median/avg/sum</option>
					      
					    </select></li>
					    <li>
					<label for="clustering">Clustering variable : </label>
					    <select name="clustering" class="clustering">
					   
					    </select></li>
					    <li>
					<label for="fill">Stacking variable : </label>
					    <select name="fill" class="fill">
					    	
					    </select></li>
					</ul> -->
				</div>
				<div class='window_middle_right'>
				</div>
			</div>
			<div class='window_bottom'>
				<label for="x">Select x-axis variable : </label>
				    <select name="x" class="x">
				     
				    </select>
			</div>
		</div>
		<div id='data_selection_scatter' class='content_window' style='display: none'>
			<div class='window_top'>
				<label class="ds_chart_text">Scatter chart</label>
				<label for="speed">Select a table : </label>
					    <select name="table_name" class="table_name">
					    	
					     	<?php for ($i=0; $i<$tables_count; $i++) { ?>
				
				    		<option><?php echo $tables[$i]['TABLE_NAME'] ?></option>
				     
							<?php } ?>
					    </select>
				<ul>
			
				</ul>
			</div>
			<div class='window_middle'>
				<div class='window_middle_left'>
					<ul>
						<li>
							<label for="y">Select y-axis variable : </label>
							    <select name="y" class="y">
							
							</select>
						</li>
						<!-- <li>
					<label for="calculations">Additional features : </label>
					    <select name="calculations" ´class="calculations">
					    	<option>Mean/median/avg/sum</option>
					      
					    </select></li> -->
					    <li>
					<label for="colour">Grouping variable : </label>
					    <select name="colour" class="colour">
					    	
					    </select></li>
					</ul>
				</div>
				<div class='window_middle_right'>
				</div>
			</div>
			<div class='window_bottom'>
				<label for="x">Select x-axis variable : </label>
				    <select name="x" class="x">
				     
				    </select>
			</div>
		</div>
		<div id='data_selection_line' class='content_window' style='display: none'>
			<div class='window_top'>
				<label class="ds_chart_text">Line chart</label>
				<label for="speed">Select a table : </label>
					    <select name="table_name" class="table_name">
					    	
					     	<?php for ($i=0; $i<$tables_count; $i++) { ?>
				
				    		<option><?php echo $tables[$i]['TABLE_NAME'] ?></option>
				     
							<?php } ?>
					    </select>
				<ul>
			
				</ul>
			</div>
			<div class='window_middle'>
				<div class='window_middle_left'>
					<ul>
						<li>
							<label for="y">Select y-axis variable : </label>
							    <select name="y" class="y">
							
							</select>
						</li>
						<!-- <li>
					<label for="calculations">Additional features : </label>
					    <select name="calculations" ´class="calculations">
					    	<option>Mean/median/avg/sum</option>
					      
					    </select></li> -->
					    <li>
					<label for="colour">Grouping variable : </label>
					    <select name="colour" class="colour">
					   
					    </select></li>
					</ul>
				</div>
				<div class='window_middle_right'>
				</div>
			</div>
			<div class='window_bottom'>
				<label for="x">Select x-axis variable : </label>
				    <select name="x" class="x">
				     
				    </select>
			</div>
		</div>
		<div id='data_selection_box' class='content_window' style='display: none'>
			<div class='window_top'>
				<label class="ds_chart_text">Box chart</label>
				<label for="speed">Select a table : </label>
					    <select name="table_name" class="table_name">
					    	
					     	<?php for ($i=0; $i<$tables_count; $i++) { ?>
				
				    		<option><?php echo $tables[$i]['TABLE_NAME'] ?></option>
				     
							<?php } ?>
					    </select>
				<ul>
			
				</ul>
			</div>
			<div class='window_middle'>
				<div class='window_middle_left'>
					<ul>
						<li>
							<label for="y">Select y-axis variable : </label>
							    <select name="y" class="y">
							
							</select>
						</li>
					    <!-- <li>
					<label for="clustering">Clustering variable : </label>
					    <select name="clustering" class="clustering">
					   
					    </select></li> -->
					</ul>
				</div>
				<div class='window_middle_right'>
				</div>
			</div>
			<div class='window_bottom'>
				<label for="x">Select x-axis variable : </label>
				    <select name="x" class="x">
				     
				    </select>
			</div>
		</div>
		
		<div id='fine_tuning_column' class='content_window' style='display: none'>
			<div class='window_middle_left_fine'>
				<div class="accordion">
	 				<h3>Range</h3>
					<div>
						<ol>
	 						<li>
						<label for='startTime'>From:</label>
							<input type="text" name="startTime" class="startTime">
							</li>
			 				<li>
						<label for='endTime'>to:</label>
							<input type="text" name="endTime" class="endTime">
							</li>
						</ol>
					</div>
	    			<!-- <h3>Color</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>y-axis variable:</label>
			 					<input type='text' name="color_y_axis" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>Clustering variable:</label>
			 					<input type='text' name="color_clustering" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>Stacking variable:</label>
			 					<input type='text' name="color_stacking" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>x-axis variable:</label>
			 					<input type='text' name="color_x_axis" class="colorpicker"/>
	 						</li>
						</ol>
	 				</div> -->
					<h3>Labels</h3>
	 				<div>
	 					<ol>
	 						<li>
								<label for='topic'>Topic </label>
									<input type="text" name="ggtitle" class="labels">
							</li>
							<li>
								<label for='x_axis'>X-axis</label>
									<input type="text" name="xlab" class="labels">
							</li>
							<li>
								<label for='y_axis'>Y-axis</label>
									<input type="text" name="ylab" class="labels">
							</li>
						</ol>
					</div>
					<h3>Devices</h3>
	 				<div>
	 					<ol class="device_list">
	 					<li>
			    			<?php
			    			echo '<input id="co_cbx_device_all" class="select_all" type="checkbox" value="">';
			    			echo '<label for="co_cbx_device_all" >Select all</label>';
			    			?>
				    	</li>
	 					<?php $co = count($devices);
	 					 	for ($i=0; $i<$co; $i++) { ?>
				
				    		<li>
				    			<?php
				    			echo '<input id="co'. $devices[$i]['device_id'] .'" class="device_checkbox" type="checkbox" value="' . $devices[$i]['device_id'] .'">';
								echo '<label class="device_label" for="co'. $devices[$i]['device_id'] .'">'. $devices[$i]['device_id'] .'</label>';
								echo '<label class="group_label">'. $devices[$i]['label'] .'</label>';
				    			?>
				    		</li>
				     
						<?php } ?>
	 					</ol>
	 				</div>
					<h3>Image size</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>Image width (inches):</label>
								<input type="number" name="width" class="limit" min="1" max="20" value="9.6">
							</li>
							<li>
								<label>Image height (inches):</label>
								<input type="number" name="height" class="limit" min="1" max="10" value="5.4">
							</li>
							<li>
								<label>Dpi:</label>
								<input type="number" name="dpi" class="limit" min="50" max="400" value="200">
	 						</li>
	 					</ol>
	 				</div>
	 				<h3>Limitations</h3>
	 				<div>
	 					<label for='limit'>Maximum number of data points:</label>
							<input type="number" name="limit" class="limit" min="0" value="1000">
	 				</div>
	 				<h3>Statistics</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<input id="co_median" class="stat_cbx" type="checkbox" value="" name="stat_median">
			 					<label for='co_median'>Show median for:</label>
			 					<select name="stat_median" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
							<li>
								<input id="co_min" class="stat_cbx" type="checkbox" value="" name="stat_min">
			 					<label for='co_min'>Show min for:</label>
			 					<select name="stat_min" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
							<li>
								<input id="co_max" class="stat_cbx" type="checkbox" value="" name="stat_max">
			 					<label for='co_max'>Show max for:</label>
			 					<select name="stat_max" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
							<li>
								<input id="co_mean" class="stat_cbx" type="checkbox" value="" name="stat_mean">
			 					<label for='co_mean'>Show mean for:</label>
			 					<select name="stat_mean" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
						</ol>
	 				</div>
				</div>
			</div>
			<div class='window_middle_right'>

			</div>
			
		</div>

		<div id='fine_tuning_pie' class='content_window' style='display: none'>
			<div class='window_middle_left_fine'>
				<div class="accordion">
				 	<h3>Range</h3>
					<div>
						<ol>
							<li>
						<label for='startTime'>From:</label>
							<input type="text" name="startTime" class="startTime">
							</li>
			 				<li>
						<label for='endTime'>to:</label>
							<input type="text" name="endTime" class="endTime">
							</li>	
	 					</ol>
					</div>
	    			<!-- <h3>Color</h3>
	 				<div>
	 					<ol>
							<li>	
								<label>Angle variable:</label>
			 					<input type='text' name="color_angle" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>Slicing variable:</label>
			 					<input type='text' name="color_slicing" class="colorpicker"/>
	 						</li>	
	 					</ol>
	 				</div> -->
					<h3>Labels</h3>
	 				<div>
	 					<ol>
	 						<li>
								<label for='topic'>Topic </label>
									<input type="text" name="ggtitle" class="labels">
							</li>
							<li>
								<label for='x_axis'>X-axis</label>
									<input type="text" name="xlab" class="labels">
							</li>
							<li>
								<label for='y_axis'>Y-axis</label>
									<input type="text" name="ylab" class="labels">
							</li>
						</ol>
					</div>
					<h3>Devices</h3>
	 				<div>
	 					<ol class="device_list">
	 					<li>
			    			<?php
			    			echo '<input id="pie_cbx_device_all" class="select_all" type="checkbox" value="">';
			    			echo '<label for="pie_cbx_device_all" >Select all</label>';
			    			?>
				    	</li>
	 					<?php $co = count($devices);
	 					 	for ($i=0; $i<$co; $i++) { ?>
				
				    		<li>
				    			<?php
				    			echo '<input id="pie'. $devices[$i]['device_id'] .'" class="device_checkbox" type="checkbox" value="' . $devices[$i]['device_id'] .'">';
								echo '<label class="device_label" for="pie'. $devices[$i]['device_id'] .'">'. $devices[$i]['device_id'] .'</label>';
								echo '<label class="group_label">'. $devices[$i]['label'] .'</label>';
				    			?>
				    		</li>
				     
						<?php } ?>
	 					</ol>
	 				</div>
					<h3>Image size</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>Image width (inches):</label>
								<input type="number" name="width" class="limit" min="1" max="20" value="9.6">
							</li>
							<li>
								<label>Image height (inches):</label>
								<input type="number" name="height" class="limit" min="1" max="10" value="5.4">
							</li>
							<li>
								<label>Dpi:</label>
								<input type="number" name="dpi" class="limit" min="50" max="400" value="200">
	 						</li>
	 					</ol>
	 				</div>
	 				<h3>Limitations</h3>
	 				<div>
	 					<label for='limit'>Maximum number of data points:</label>
							<input type="number" name="limit" class="limit" min="0" value="1000">
	 				</div>
				</div>
			</div>
			<div class='window_middle_right'>

			</div>
		</div>

		<div id='fine_tuning_histogram' class='content_window' style='display: none'>
			<div class='window_middle_left_fine'>
				<div class="accordion">
				 	<h3>Range</h3>
					<div>
						<ol>
			 				<li>
						<label for='startTime'>From:</label>
							<input type="text" name="startTime" class="startTime">
							</li>
							<li>
						<label for='endTime'>to:</label>
							<input type="text" name="endTime" class="endTime">
							</li>
						</ol>
					</div>
	    			<!-- <h3>Color</h3>
	 				<div>
	 					<ol>
			 				<li>
			 					<label>x-axis variable:</label>
			 					<input type='text' name="color_x_axis" class="colorpicker"/>
	 						</li>
						</ol>
	 				</div> -->
					<h3>Labels</h3>
	 				<div>
	 					<ol>
	 						<li>
								<label for='topic'>Topic </label>
									<input type="text" name="ggtitle" class="labels">
							</li>
							<li>
								<label for='x_axis'>X-axis</label>
									<input type="text" name="xlab" class="labels">
							</li>
							<li>
								<label for='y_axis'>Y-axis</label>
									<input type="text" name="ylab" class="labels">
							</li>
						</ol>
					</div>
					<h3>Devices</h3>
	 				<div>
	 					<ol class="device_list">
	 					<li>
			    			<?php
			    			echo '<input id="hist_cbx_device_all" class="select_all" type="checkbox" value="">';
			    			echo '<label for="hist_cbx_device_all" >Select all</label>';
			    			?>
				    	</li>
	 					<?php $co = count($devices);
	 					 	for ($i=0; $i<$co; $i++) { ?>
				
				    		<li>
				    			<?php
				    			echo '<input id="hist'. $devices[$i]['device_id'] .'" class="device_checkbox" type="checkbox" value="' . $devices[$i]['device_id'] .'">';
								echo '<label class="device_label" for="hist'. $devices[$i]['device_id'] .'">'. $devices[$i]['device_id'] .'</label>';
								echo '<label class="group_label">'. $devices[$i]['label'] .'</label>';
				    			?>
				    		</li>
				     
						<?php } ?>
	 					</ol>
	 				</div>
					<h3>Image size</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>Image width (inches):</label>
								<input type="number" name="width" class="limit" min="1" max="20" value="9.6">
							</li>
							<li>
								<label>Image height (inches):</label>
								<input type="number" name="height" class="limit" min="1" max="10" value="5.4">
							</li>
							<li>
								<label>Dpi:</label>
								<input type="number" name="dpi" class="limit" min="50" max="400" value="200">
	 						</li>
	 					</ol>
	 				</div>
	 				<h3>Limitations</h3>
	 				<div>
	 					<label for='limit'>Maximum number of data points:</label>
							<input type="number" name="limit" class="limit" min="0" value="1000">
	 				</div>
				</div>
			</div>
			<div class='window_middle_right'>

			</div>
		</div>

		<div id='fine_tuning_scatter' class='content_window' style='display: none'>
			<div class='window_middle_left_fine'>
				<div class="accordion">
					<h3>Range</h3>
					<div>
						<ol>
	 						<li>
						<label for='startTime'>From:</label>
							<input type="text" name="startTime" class="startTime">
							</li>
			 				<li>
						<label for='endTime'>to:</label>
							<input type="text" name="endTime" class="endTime">
							</li>
						</ol>
					</div>
					<!-- <h3>Color</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>y-axis variable:</label>
			 					<input type='text' name="color_y_axis" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>Grouping variable:</label>
	 							<input type='text' name="color_grouping" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>x-axis variable:</label>
			 					<input type='text' name="color_x_axis" class="colorpicker"/>
	 						</li>
						</ol>
	 				</div> -->
					<h3>Labels</h3>
	 				<div>
	 					<ol>
	 						<li>
								<label for='topic'>Topic </label>
									<input type="text" name="ggtitle" class="labels">
							</li>
							<li>
								<label for='x_axis'>X-axis</label>
									<input type="text" name="xlab" class="labels">
							</li>
							<li>
								<label for='y_axis'>Y-axis</label>
									<input type="text" name="ylab" class="labels">
							</li>
						</ol>
					</div>
					<h3>Devices</h3>
	 				<div>
	 					<ol class="device_list">
	 					<li>
			    			<?php
			    			echo '<input id="scatter_cbx_device_all" class="select_all" type="checkbox" value="">';
			    			echo '<label for="scatter_cbx_device_all" >Select all</label>';
			    			?>
				    	</li>
	 					<?php $co = count($devices);
	 					 	for ($i=0; $i<$co; $i++) { ?>
				
				    		<li>
				    			<?php
				    			echo '<input id="scatter'. $devices[$i]['device_id'] .'" class="device_checkbox" type="checkbox" value="' . $devices[$i]['device_id'] .'">';
								echo '<label class="device_label" for="scatter'. $devices[$i]['device_id'] .'">'. $devices[$i]['device_id'] .'</label>';
								echo '<label class="group_label">'. $devices[$i]['label'] .'</label>';
				    			?>
				    		</li>
				     
						<?php } ?>
	 					</ol>
	 				</div>
					<h3>Image size</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>Image width (inches):</label>
								<input type="number" name="width" class="limit" min="1" max="20" value="9.6">
							</li>
							<li>
								<label>Image height (inches):</label>
								<input type="number" name="height" class="limit" min="1" max="10" value="5.4">
							</li>
							<li>
								<label>Dpi:</label>
								<input type="number" name="dpi" class="limit" min="50" max="400" value="200">
	 						</li>
	 					</ol>
	 				</div>
	 				<h3>Limitations</h3>
	 				<div>
	 					<label for='limit'>Maximum number of data points:</label>
							<input type="number" name="limit" class="limit" min="0" value="1000">
	 				</div>
	 				<h3>Statistics</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<input id="co_median" class="stat_cbx" type="checkbox" value="" name="stat_median">
			 					<label for='co_median'>Show median for:</label>
			 					<select name="stat_median" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
							<li>
								<input id="co_min" class="stat_cbx" type="checkbox" value="" name="stat_min">
			 					<label for='co_min'>Show min for:</label>
			 					<select name="stat_min" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
							<li>
								<input id="co_max" class="stat_cbx" type="checkbox" value="" name="stat_max">
			 					<label for='co_max'>Show max for:</label>
			 					<select name="stat_max" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
							<li>
								<input id="co_mean" class="stat_cbx" type="checkbox" value="" name="stat_mean">
			 					<label for='co_mean'>Show mean for:</label>
			 					<select name="stat_mean" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
						</ol>
	 				</div>
				</div>
			</div>
			<div class='window_middle_right'>

			</div>
		</div>

		<div id='fine_tuning_line' class='content_window' style='display: none'>
			<div class='window_middle_left_fine'>
				<div class="accordion">
					<h3>Range</h3>
					<div>
						<ol>
	 						<li>
						<label for='startTime'>From:</label>
							<input type="text" name="startTime" class="startTime">
							</li>
			 				<li>
						<label for='endTime'>to:</label>
							<input type="text" name="endTime" class="endTime">
							</li>
						</ol>
					</div>
					<!-- <h3>Color</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>y-axis variable:</label>
			 					<input type='text' name="color_y_axis" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>Clustering variable:</label>
	 							<input type='text' name="color_clustering" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>x-axis variable:</label>
			 					<input type='text' name="color_x_axis" class="colorpicker"/>
	 						</li>
						</ol>
	 				</div> -->
					<h3>Labels</h3>
	 				<div>
	 					<ol>
	 						<li>
								<label for='topic'>Topic </label>
									<input type="text" name="ggtitle" class="labels">
							</li>
							<li>
								<label for='x_axis'>X-axis</label>
									<input type="text" name="xlab" class="labels">
							</li>
							<li>
								<label for='y_axis'>Y-axis</label>
									<input type="text" name="ylab" class="labels">
							</li>
						</ol>
					</div>
					<h3>Devices</h3>
	 				<div>
	 					<ol class="device_list">
	 					<li>
			    			<?php
			    			echo '<input id="line_cbx_device_all" class="select_all" type="checkbox" value="">';
			    			echo '<label for="line_cbx_device_all" >Select all</label>';
			    			?>
				    	</li>
	 					<?php $co = count($devices);
	 					 	for ($i=0; $i<$co; $i++) { ?>
				
				    		<li>
				    			<?php
				    			echo '<input id="line'. $devices[$i]['device_id'] .'" class="device_checkbox" type="checkbox" value="' . $devices[$i]['device_id'] .'">';
								echo '<label class="device_label" for="line'. $devices[$i]['device_id'] .'">'. $devices[$i]['device_id'] .'</label>';
								echo '<label class="group_label">'. $devices[$i]['label'] .'</label>';
				    			?>
				    		</li>
				     
						<?php } ?>
	 					</ol>
	 				</div>
					<h3>Image size</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>Image width (inches):</label>
								<input type="number" name="width" class="limit" min="1" max="20" value="9.6">
							</li>
							<li>
								<label>Image height (inches):</label>
								<input type="number" name="height" class="limit" min="1" max="10" value="5.4">
							</li>
							<li>
								<label>Dpi:</label>
								<input type="number" name="dpi" class="limit" min="50" max="400" value="200">
	 						</li>
	 					</ol>
	 				</div>
	 				<h3>Limitations</h3>
	 				<div>
	 					<label for='limit'>Maximum number of data points:</label>
							<input type="number" name="limit" class="limit" min="0" value="1000">
	 				</div>
	 				<h3>Statistics</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<input id="co_median" class="stat_cbx" type="checkbox" value="" name="stat_median">
			 					<label for='co_median'>Show median for:</label>
			 					<select name="stat_median" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
							<li>
								<input id="co_min" class="stat_cbx" type="checkbox" value="" name="stat_min">
			 					<label for='co_min'>Show min for:</label>
			 					<select name="stat_min" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
							<li>
								<input id="co_max" class="stat_cbx" type="checkbox" value="" name="stat_max">
			 					<label for='co_max'>Show max for:</label>
			 					<select name="stat_max" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
							<li>
								<input id="co_mean" class="stat_cbx" type="checkbox" value="" name="stat_mean">
			 					<label for='co_mean'>Show mean for:</label>
			 					<select name="stat_mean" class="stat_select">
						    		<option>x</option>
						    		<option>y</option>
							    </select>
							</li>
						</ol>
	 				</div>
				</div>
			</div>
			<div class='window_middle_right'>

			</div>
		</div>

		<div id='fine_tuning_box' class='content_window' style='display: none'>
			<div class='window_middle_left_fine'>
				<div class="accordion">
					<h3>Range</h3>
					<div>
						<ol>
	 						<li>
						<label for='startTime'>From:</label>
							<input type="text" name="startTime" class="startTime">
							</li>
			 				<li>
						<label for='endTime'>to:</label>
							<input type="text" name="endTime" class="endTime">
							</li>
						</ol>
					</div>
					<!-- <h3>Color</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>y-axis variable:</label>
			 					<input type='text' name="color_y_axis" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>Grouping variable:</label>
	 							<input type='text' name="color_grouping" class="colorpicker"/>
			 				</li>
			 				<li>
			 					<label>x-axis variable:</label>
			 					<input type='text' name="color_x_axis" class="colorpicker"/>
	 						</li>
						</ol>
	 				</div> -->
					<h3>Labels</h3>
	 				<div>
	 					<ol>
	 						<li>
								<label for='topic'>Topic </label>
									<input type="text" name="ggtitle" class="labels">
							</li>
							<li>
								<label for='x_axis'>X-axis</label>
									<input type="text" name="xlab" class="labels">
							</li>
							<li>
								<label for='y_axis'>Y-axis</label>
									<input type="text" name="ylab" class="labels">
							</li>
						</ol>
					</div>
					<h3>Devices</h3>
	 				<div>
	 					<ol class="device_list">
	 					<li>
			    			<?php
			    			echo '<input id="box_cbx_device_all" class="select_all" type="checkbox" value="">';
			    			echo '<label for="box_cbx_device_all" >Select all</label>';
			    			?>
				    	</li>
	 					<?php $co = count($devices);
	 					 	for ($i=0; $i<$co; $i++) { ?>
				
				    		<li>
				    			<?php
				    			echo '<input id="box'. $devices[$i]['device_id'] .'" class="device_checkbox" type="checkbox" value="' . $devices[$i]['device_id'] .'">';
								echo '<label class="device_label" for="box'. $devices[$i]['device_id'] .'">'. $devices[$i]['device_id'] .'</label>';
								echo '<label class="group_label">'. $devices[$i]['label'] .'</label>';
				    			?>
				    		</li>
				     
						<?php } ?>
	 					</ol>
	 				</div>
					<h3>Image size</h3>
	 				<div>
	 					<ol>
	 						<li>
			 					<label>Image width (inches):</label>
								<input type="number" name="width" class="limit" min="1" max="20" value="9.6">
							</li>
							<li>
								<label>Image height (inches):</label>
								<input type="number" name="height" class="limit" min="1" max="10" value="5.4">
							</li>
							<li>
								<label>Dpi:</label>
								<input type="number" name="dpi" class="limit" min="50" max="400" value="200">
	 						</li>
	 					</ol>
	 				</div>
	 				<h3>Limitations</h3>
	 				<div>
	 					<label for='limit'>Maximum number of data points:</label>
							<input type="number" name="limit" class="limit" min="0" value="1000">
	 				</div>
				</div>
			</div>
			<div class='window_middle_right'>

			</div>
		</div>
		
	</div>
	
	<div class='bottom_button_bar'>
		<ol>
			<li><div id="cancel">Cancel</div></li>
			<li><div id="ft">Fine Tuning</div></li>
			<li><div id="next">Next</div></li>
		</ol>
	</div>
</div>