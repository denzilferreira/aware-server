<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AWARE - Android Mobile Context Framework</title>
<link rel="stylesheet" type="text/css" href="<?php echo $this->config->base_url('application/views'); ?>/css/aware.css" />
<script type="text/javascript" src="<?php echo $this->config->base_url('application/views'); ?>/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?php echo $this->config->base_url('application/views'); ?>/js/aware.js"></script>
</head>
<body>
	<img src="http://www.awareframework.com/home/wp-content/uploads/2013/03/header_logo.png" width="384"/>
	<h1>AWARE Authentication</h1>

	<div class="container" style="text-align:center; margin:auto">
		<br>
		<br>
		<?php echo anchor('auth/session/google', img(array('src' => 'application/images/sign-in-with-google.png', 'alt' => 'Sign in with Google'))); ?>
	</div>

</body>
</html>