<?php
global $action;
global $model;
?>
<div class="scene newsletter i:newsletter">

	<h1>Newsletter</h1>
	<p>
		Enter your email below to sign up for newsletter.
	</p>

	<?= $model->formStart("subscribe", array("class" => "labelstyle:inject")) ?>

<?	if(message()->hasMessages(array("type" => "error"))): ?>
		<p class="errormessage">
<?		$messages = message()->getMessages(array("type" => "error"));
		message()->resetMessages();
		foreach($messages as $message): ?>
			<?= $message ?><br>
<?		endforeach;?>
		</p>
<?	endif; ?>

		<fieldset>
			<?= $model->input("newsletter", array("type" => "hidden", "value" => "curious")); ?>
			<?= $model->input("email", array("required" => true)); ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Subscribe", array("class" => "primary", "wrapper" => "li.signup")) ?>
		</ul>
	<?= $model->formEnd() ?>

	<p>
		Right after sign up, you'll receive an email with an activation link and unsubsribe information.
	</p>

</div>
