<h1>Login success</h1>

<?php
	echo 	"<pre>".
			"First name: " . $this->session->userdata['first_name'] . "\n".
			"Last name: " . $this->session->userdata['last_name'] . "\n".
			"UID: " . $this->session->userdata['google_id']. "\n".
			"Email: " . $this->session->userdata['email'].
			
			"\n\n". anchor('dashboard/logout', 'Log out');

?>