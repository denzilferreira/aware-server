<div id="sub-menu">
	<ul id="sub-menu-container">
			<li class='menu-item'><?php echo anchor('developer', 'Plugins'); ?></li>
			<?php
			if ($this->uri->segment(3) == 'new') {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span>Create new plugin</li>";
			}
			else if ($this->uri->segment(2) == 'edit_plugin') {
				echo "<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span>Edit plugin</li>";
			}
			else if (isset($plugin_data)) {
				echo 	"<li class='menu-item-breadcrumb'><span style='margin-right: 7px;'>&raquo;</span>" . 
						anchor('developer/plugin/'.$plugin_data['id'], $plugin_data['title']) . 
						"</li>";
			}
			?>
	</ul>
</div>