<div id="sub-menu">
	<ul id="sub-menu-container">
			<li class='menu-item-breadcrumb'><?php echo anchor('researcher/studies', 'Studies'); ?></li>
			<?php 
			if (isset($study_data)) {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span>" . 
					anchor('researcher/study/'.$study_data['id'], $study_data['study_name']) . 
					"</li>";
			}
			if ($this->uri->segment(2) == 'new_study') {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span>Create new study</li>";
			}
			?>
	</ul>
</div>