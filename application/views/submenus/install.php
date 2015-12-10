<div id="sub-menu">
	<ul id="sub-menu-container">
			<li class='menu-item-breadcrumb'><?php echo anchor('install/start', 'Start'); ?></li>
			
			<?php 
			if ($this->uri->segment(2) == 'step1') {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'><b>" . anchor('install/step1', 'Step 1') . "</b></a></li>";
			}
			if ($this->uri->segment(2) == 'step2') {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'>" . anchor('install/step1', 'Step 1') . "</a></li>";
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'><b>" . anchor('install/step2', 'Step 2') . "</b></a></li>";
			}
			if ($this->uri->segment(2) == 'step3') {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'>" . anchor('install/step1', 'Step 1') . "</a></li>";
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'>" . anchor('install/step2', 'Step 2') . "</a></li>";
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'><b>" . anchor('install/step3', 'Step 3') . "</b></a></li>";
			}
			
			if ($this->uri->segment(2) == 'finish') {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'>" . anchor('install/step1', 'Step 1') . "</a></li>";
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'>" . anchor('install/step2', 'Step 2') . "</a></li>";
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'>" . anchor('install/step3', 'Step 3') . "</a></li>";
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span><a href='#'><b>" . anchor('install/finish', 'Finish') . "</b></a></li>";
			}
			?>
	</ul>
</div>