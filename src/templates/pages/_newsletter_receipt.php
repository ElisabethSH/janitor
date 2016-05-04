<?php
global $action;
global $model;

$email = session()->value("signup_email");
	
?>
<div class="scene newsletter i:scene">

	<h1>Thank you!</h1>
	<p>You are almost home.</p>
	<p>
		We have sent a verification email to <em><?= $email ?></em> with an
		activation link. Check your inbox and click the link to activate your newsletter subscription.
	</p>
	<p>
		Signing up for the newsletter, includes a getting a member account to manage your
		newsletter subscription. You'll find login information in the verification email.
	</p>

	<p>See you again soon.</p>

</div>
