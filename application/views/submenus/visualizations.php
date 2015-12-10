<div id="sub-menu">
	<ul id="sub-menu-container">
		<li class='menu-item-breadcrumb'><?php echo anchor('researcher/studies', 'Studies'); ?></li>
			<?php 
			if (isset($study_data)) {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span>" . 
					anchor('researcher/study/'.$study_data['id'], $study_data['study_name']) . 
					"</li>";
			}
			echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span>".anchor('visualizations/study/'.$study_data['id'], 'Visualizations')."</li>";
			if ($this->uri->segment(2) == 'new_chart') {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span>".anchor('visualizations/new_chart/'.$study_data['id'], 'New chart')."</li>";
			}
			if ($this->uri->segment(2) == 'edit_chart') {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span>".anchor('visualizations/edit_chart/'.$study_data['id']."/".$chart_id, 'Edit chart')."</li>";
			}
			?>
	</ul>
</div>