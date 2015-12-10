<?php

$errors = $this->session->flashdata('errors');
$post_data_study = $this->session->flashdata('post_data_study');
$post_data_db = $this->session->flashdata('post_data_db');

?>

<h1>Create a new study</h1>

<div style="margin-bottom:10px;">
Please fill in all the fields. Notice that you cannot edit the study name after creating the study.
</div>

<?php
	echo form_open('researcher/create_new_study', array('id' => 'new-study'));
?>

<div class="new-study-titles" >Study name</div>
<div class="new-study-values">
	<input id="study_name" type="text" maxlength="100" name="study_name" autocomplete="off" placeholder="Study Name (max. 100 chars)" value="<?= isset($post_data_study['name']) ? $post_data_study['name'] : '' ?>">
	<?php 
		if (isset($errors['study_name'])) {
			echo '<span class="error-msg">' . $errors['study_name'] . '</span>';
		}
	?>
</div>

<?php echo form_error('study_name'); ?>

<div class="new-study-titles" >Study description</div>
<div class="new-study-values">
	<textarea id="study_description" maxlength="4000" placeholder="Study Description (max. 4000 chars)" name="study_description"><?= isset($post_data_study['description']) ? $post_data_study['description'] : '' ?></textarea>
	<?php 
		if (isset($errors['study_description'])) {
			echo '<span class="error-msg">' . $errors['study_description'] . '</span>';
		}
	?>
</div>

<?php echo form_error('study_description'); ?>

<?php echo form_error('study_config'); ?>

<div class="new-study-titles" >Study database</div></td>
	<div class="new-study-values">
	
		<?php
			$host_types = array(
							'aware' => 'Aware server',
							'remote' => 'Remote server',
						);
			$host_selected = $this->session->flashdata('host-type') ? $this->session->flashdata('host-type') : 'aware';

			echo form_dropdown('host-type', $host_types, $host_selected, 'id="host-type"');
		?>
	
		<div id="db-choices">
	
			<div class="db-choice aware">
				<p>Study data will be hosted on Aware Framework's server.</p>
			</div>
			
			<div class="db-choice remote">
			
				<p>Study data will be hosted on server of your selection.</p>
				<table>
					<tr>
						<td class='title'>Hostname:</td>
						<td class='value'>
							<input id="db_hostname" type="text" name="db_hostname" placeholder="Hostname" autocomplete="off" value="<?= isset($post_data_db['hostname']) ? $post_data_db['hostname'] : '' ?>">
							<?php 
								if (isset($errors['db_hostname'])) {
									echo '<span class="error-msg">' . $errors['db_hostname'] . '</span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td class='title'>Port:</td>
						<td class='value'>
							<input id="db_port" type="text" maxlength="5" name="db_port" placeholder="Port (default: 3306)" autocomplete="off" value="<?=isset($post_data_db['port']) ? $post_data_db['port'] : '' ?>">
							<?php 
								if (isset($errors['db_port'])) {
									echo '<span class="error-msg">' . $errors['db_port'] . '</span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td class='title'>Database:</td>
						<td class='value'>
							<input id="db_name" type="text" name="db_name" placeholder="Name of the database" autocomplete="off" value="<?=isset($post_data_db['name']) ? $post_data_db['name'] : '' ?>">
							<?php 
								if (isset($errors['db_name'])) {
									echo '<span class="error-msg">' . $errors['db_name'] . '</span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td class='title'>Username:</td>
						<td class='value'>
							<input id="db_username" type="text" name="db_username" placeholder="Username" autocomplete="off" value="<?=isset($post_data_db['username']) ? $post_data_db['username'] : '' ?>">
							<?php 
								if (isset($errors['db_username'])) {
									echo '<span class="error-msg">' . $errors['db_username'] . '</span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td class='title'>Password:</td>
						<td class='value'>
							<input id="db_password" type="password" name="db_password" placeholder="Password" autocomplete="off">
							<?php 
								if (isset($errors['db_password'])) {
									echo '<span class="error-msg">' . $errors['db_password'] . '</span>';
								}
							?>
						</td>
					</tr>
					<?php 
						if (isset($errors['database'])) {
							echo 	'<tr>
										<td class="value" colspan="2"><span class="error-msg" style="float: none; margin-left: 0;">' . $errors['database'] . '</span></td>
									</tr>';
						}
					?>
				</table>
				
				<input id="newstudy_testbutton" type="submit" value="Test connection" name="dbtest">
				
				<p id="connection-success" class="success-msg">Connection success!</p>
				<p id="connection-error" class="error-msg">Connection error!</p>
				<?php 
					if (isset($errors['connection-error'])) {
						//echo '<p id="connection-error" class="error-msg">Connection error!</p>';
					}
				?>
			</div>
		</div>
	</div>	
	
	


<div style="clear:both" class="action-bar">
	<a href="<?php echo site_url('researcher'); ?>" style="float:left">Cancel</a>
	<input class="newstudy_submitbutton" type="submit" value="Create" name="submit">
</div>



<div id="esmq-questionnaires" style="display: none;">
  <ul>
  	<li><a href="#esmq-0" id="add-new">Add new</a></li>
  </ul>

  <p class="esmq-info">Add a new questionnaire by clicking "Add new" -button</p>
</div>

<?php
	echo form_close();
?>
