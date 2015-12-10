<div id="sub-menu">
	<ul id="sub-menu-container">
		<?php
			if ($this->uri->segment(2) == "plugins" || $this->uri->segment(2) == "") {
				echo "<li class='menu-item'><b>" . anchor('manager/plugins', 'Plugins') . "</b></li>";
			} else {
				echo "<li class='menu-item'>" . anchor('manager/plugins', 'Plugins') . "</li>";
			}
			if ($this->uri->segment(2) == "studies") {
				echo "<li class='menu-item'><b>" . anchor('manager/studies', 'Studies') . "</b></li>";
			} else {
				echo "<li class='menu-item'>" . anchor('manager/studies', 'Studies') . "</li>";
			}
			if ($this->uri->segment(2) == "user_management") {
				echo "<li class='menu-item'><b>" . anchor('manager/user_management', 'User management') . "</b></li>";
			} else {
				echo "<li class='menu-item'>" . anchor('manager/user_management', 'User management') . "</li>";
			}
		?>
	</ul>
</div>