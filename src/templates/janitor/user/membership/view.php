<?php
global $action;
global $model;
$SC = new Shop();

$user_id = $action[2];


$user = $model->getUsers(array("user_id" => $user_id));
$membership = $model->getMembers(array("user_id" => $user_id));


// FOR TESTING EMAIL SENDING
// $subscription = $model->getSubscriptions(array("subscription_id" => $membership["subscription_id"]));
// $IC = new Items();
// $mem = $IC->typeObject("membership");
// $mem->subscribed($subscription);

?>
<div class="scene i:scene defaultView userMembership">
	<h1>Membership</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>


	<?= $JML->userTabs($user_id, "membership") ?>

	
	<div class="item">
	<? if($membership): ?>

		<? if($membership["subscription_id"]):
			$subscription = $model->getSubscriptions(array("subscription_id" => $membership["subscription_id"])); ?>
		<h3><?= $membership["item"]["name"] ?></h3>
		<? else: ?>
		<h3>Inactive membership</h3>
		<? endif; ?>

		
		<dl class="info">
			<dt class="created_at">Created at</dt>
			<dd class="created_at"><?= date("d. F, Y", strtotime($membership["created_at"])) ?></dd>

		<? if($membership["modified_at"]): ?>
			<dt class="modified_at">Modified at</dt>
			<dd class="modified_at"><?= date("d. F, Y", strtotime($membership["modified_at"])) ?></dd>
		<? endif; ?>

		<? if($membership["renewed_at"]): ?>
			<dt class="renewed_at">Renewed at</dt>
			<dd class="renewed_at"><?= date("d. F, Y", strtotime($membership["renewed_at"])) ?></dd>
		<? endif; ?>

		<? if($membership["expires_at"]): ?>
			<dt class="expires_at">Expires at</dt>
			<dd class="expires_at"><?= date("d. F, Y", strtotime($membership["expires_at"])) ?></dd>
		<? endif; ?>


		<? if($membership["item"]["prices"]):
			$offer = arrayKeyValue($membership["item"]["prices"], "type", "offer");
			$default = arrayKeyValue($membership["item"]["prices"], "type", "default");
			?>
		
			<? if($offer !== false && $default !== false): ?>
				<dt class="price default">Normal price</dt>
				<dd class="price default"><?= formatPrice($membership["item"]["prices"][$default]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
				<dt class="price offer">Special offer</dt>
				<dd class="price offer"><?= formatPrice($membership["item"]["prices"][$offer]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<? elseif($default !== false): ?>
				<dt class="price">Price</dt>
				<dd class="price"><?= formatPrice($membership["item"]["prices"][$default]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<? endif; ?>

			<dt class="payment_method">Payment method</dt>
			<dd class="payment_method"><?= $subscription["payment_method"] ? $subscription["payment_method"]["name"] : "N/A" ?></dd>
		<? endif; ?>

		<? if($membership["order_id"]): ?>
			<dt class="payment_status">Payment status</dt>
			<dd class="payment_status<?= $membership["order"]["payment_status"] < 2 ? " missing" : "" ?>"><?= $SC->payment_statuses[$membership["order"]["payment_status"]] ?></dd>
		<? endif; ?>

		</dl>

	</div>
	
	<div class="item change">
		<h2>Change your membership</h2>
		<? if($membership["order"] && $membership["order"]["payment_status"] == 2): ?>

		<p>
			There are two ways of changing your membership.
		</p>


		<div class="option">
			<h3>Upgrade the existing membership</h3>
			<p>
				- adds an order for the price difference and maintains the current renewal cyclus. Best option for membership upgrade.
			</p>
			<ul class="actions">
				<?= $HTML->link("Upgrade membership", "/janitor/admin/user/membership/upgrade/".$user_id, array("class" => "button", "wrapper" => "li.edit")) ?>
			</ul>
		</div>

		<div class="option">
			<h3>Switch to a new membership</h3>
			<p>
				- cancel the existing membership and add a new one, starting today. Best option for membership downgrade.
			</p>
			<ul class="actions">
				<?= $HTML->link("New membership", "/janitor/admin/user/membership/switch/".$user_id, array("class" => "button", "wrapper" => "li.edit")) ?>
			</ul>
		</div>


		<p>
			If you have any questions or want to cancel your account entirely, please contact 
			us directly on <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.
		</p>

		<? elseif(!$membership["subscription_id"]): ?>

		<p>
			You can add a membership through the website.<br />
		</p>

		<? else: ?>

		<p>
			You cannot change your membership until the current subscription has been paid.<br />
		</p>
		<ul class="actions">
			<?= $HTML->link("Pay now", "/janitor/admin/shop/order/payment/new/".$membership["order"]["id"], array("class" => "button primary", "wrapper" => "li.pay")); ?>
		</ul>

		<? endif; ?>

	<? else: ?>
		<p>This user does not have a membership.</p>
	<? endif; ?>
	</div>

</div>