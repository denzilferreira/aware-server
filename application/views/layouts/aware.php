<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $template['title']; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>application/views/css/style.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>application/views/css/manager.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>application/views/css/researcher.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>application/views/css/tablesorter.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>application/views/css/jquery-ui-custom.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>application/views/js/general.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>application/views/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>application/views/js/jquery.tablesorter.widgets.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>application/views/js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>application/views/js/jquery.filtertable.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>application/views/js/jquery.cookie.js"></script>
<?php echo $template['metadata']; ?>
</head>
<body>
<noscript><div id="browser_aler">This site requires javascript. Please enable javascript on your browser to use this site.</div></noscript>
	<div id="wrapper">
		<?php 
			if( $this->session->userdata('google_id') ) {
		?>
		<div id="menu">
			<ul id="menu-container">
				<?php 
					// Display navigation elements according user level
					if ($this->session->userdata('developer')) {
						if ($this->uri->segment(1) == "developer") {
							echo "<li class=\"menu-item\"><b>". anchor('developer', 'DEVELOPER') . "</b></li>\n";
						}
						else {
							echo "<li class=\"menu-item\">". anchor('developer', 'DEVELOPER') . "</li>\n";
						}						
					}
					if ($this->session->userdata('researcher')) {
						if ($this->uri->segment(1) == "researcher") {
							echo "<li class=\"menu-item\"><b>" . anchor('researcher', 'RESEARCHER') . "</b></li>\n";
						}
						else {
							echo "<li class=\"menu-item\">" . anchor('researcher', 'RESEARCHER') . "</li>\n";
						}
					}
					if ($this->session->userdata('manager')) {
						if ($this->uri->segment(1) == "manager") {
							echo "<li class=\"menu-item\"><b>" . anchor('manager', 'MANAGER') . "</b></li>\n";
						}
						else {
							echo "<li class=\"menu-item\">" . anchor('manager', 'MANAGER') . "</li>\n";
						}
					}
					if ($this->session->userdata('google_id')) {
						echo "<li class=\"menu-item menu-item-logout\">" . anchor('dashboard/logout', 'LOG OUT') . "</li>\n";
					}
				?>

			</ul>
		</div>
		<?php } ?>
		
		<?php if (isset($template['partials']['submenu'])) {echo $template['partials']['submenu']; } ?>

		<div id="content">
			<?php echo $template['body']; ?>
		</div>		
	</div>
</body>
</html>