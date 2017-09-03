<?php
global $model;

$db_check = $model->checkDatabaseSettings();

?>
<div class="scene database i:database">

	<h1>Janitor configuration</h1>
	<h2>Database settings</h2>
	<ul class="actions">
		<?= $JML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>


<? if($model->db_ok): ?>

	<h3>Database status: OK</h3>
	<p>Your database is already configured correctly.</p>

	<?= $model->formStart("/janitor/admin/setup/database/updateDatabaseSettings", array("class" => "force labelstyle:inject")) ?>

<? 		if($model->db_exists): ?>

	<p>Are you sure you want to use <em class="system_warning"><?= $model->db_janitor_db ?></em>. It already exists.</p>

	<?= $model->input("force_db", array("type" => "hidden", "value" => $model->db_janitor_db)) ?>

<? 		endif; ?>

	<ul class="actions">
		<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
	</ul>

	<?= $model->formEnd() ?>

<? endif;?>

	<?= $model->formStart("/janitor/admin/setup/database/updateDatabaseSettings", array("class" => "database labelstyle:inject")) ?>

	<h3>Root database information</h3>
	<p>Setting up a new database requires an Admin user with permission to create the project database.</p>

<? if($model->db_admin_error && !$model->wrong_db_user_password): ?>

	<h3>Connection error</h3>
	<p class="system_error">Janitor cannot connect to your admin account with the information provided.</p>

<? endif;?>


	<fieldset>

		<?= $model->input("db_host", array("value" => $model->db_host)) ?>
		<?= $model->input("db_root_user", array("value" => $model->db_root_user, "required" => ($db_check ? false : true))) ?>
		<?= $model->input("db_root_pass", array("value" => $model->db_root_pass)) ?>
	</fieldset>

	<h3>New Janitor database</h3>
	<p>Specify new database name, username and password. Feel free to use a random password - the information will be saved in connect_db.php, so you don't need to remember it or write it down.</p>

<? if($model->wrong_db_user_password): ?>

	<h3>Connection error</h3>
	<p class="system_error"><em><?= $model->db_janitor_user ?></em> already exists – but the password doesn't match.</p>

<? elseif($model->db_user_error): ?>

	<h3>Connection error</h3>
	<p class="system_error">Janitor could not log in, using the provided information.</p>

<? endif;?>

	<fieldset>

		<?= $model->input("db_janitor_db", array("value" => $model->db_janitor_db)) ?>
		<?= $model->input("db_janitor_user", array("value" => $model->db_janitor_user)) ?>
		<?= $model->input("db_janitor_pass", array("value" => $model->db_janitor_pass)) ?>
	</fieldset>
	<ul class="actions">
		<?= $model->submit("Update and continue", array("wrapper" => "li.save", "class" => "primary")) ?>
	</ul>

	<p class="note">Don't let the browser save the passwords used in this page if prompted. These passwords are associated with the database connection and not the website.</p>

	<?= $model->formEnd() ?>


</div>