<?php
global $action;
global $model;


$user_id = session()->value("user_id");
$IC = new Items();
$SC = new Shop();

// get current user
$item = $model->getUser();

$subscriptions = false;

if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS) {

	$subscriptions = $model->getSubscriptions();
}

?>
<div class="scene i:scene defaultList userSubscriptions profileSubscriptions">
	<h1>Subscriptions</h1>
	<h2><?= $item["nickname"] ?></h2>

	<?= $JML->profileTabs("subscriptions") ?>



	<div class="all_items subscriptions i:defaultList filters">
		<? if($subscriptions): ?>

		<h2>Subscriptions</h2>
		<ul class="items subscriptions">
			<? foreach($subscriptions as $subscription): ?>
			<li class="item subscription">
				<h3><?= $subscription["item"]["name"] ?></h3>
				
				<dl class="info">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= date("d. F, Y", strtotime($subscription["created_at"])) ?></dd>

				<? if($subscription["renewed_at"]): ?>
					<dt class="renewed_at">Last renewed at</dt>
					<dd class="renewed_at"><?= date("d. F, Y", strtotime($subscription["renewed_at"])) ?></dd>
				<? endif; ?>

				<? if($subscription["expires_at"]): ?>
					<dt class="expires_at">Expires at</dt>
					<dd class="expires_at"><?= date("d. F, Y", strtotime($subscription["expires_at"])) ?></dd>
				<? endif; ?>


				<? if($subscription["item"]["prices"]):
					$offer = arrayKeyValue($subscription["item"]["prices"], "type", "offer");
					$default = arrayKeyValue($subscription["item"]["prices"], "type", "default");
					?>
				
					<? if($offer !== false && $default !== false): ?>
						<dt class="price default">Normal price</dt>
						<dd class="price default"><?= formatPrice($subscription["item"]["prices"][$default]).($subscription["item"]["subscription_method"] ? " / " . $subscription["item"]["subscription_method"]["name"] : "") ?></dd>
						<dt class="price offer">Special offer</dt>
						<dd class="price offer"><?= formatPrice($subscription["item"]["prices"][$offer]).($subscription["item"]["subscription_method"] ? " / " . $subscription["item"]["subscription_method"]["name"] : "") ?></dd>
					<? elseif($default !== false): ?>
						<dt class="price">Price</dt>
						<dd class="price"><?= formatPrice($subscription["item"]["prices"][$default]).($subscription["item"]["subscription_method"] ? " / " . $subscription["item"]["subscription_method"]["name"] : "") ?></dd>
					<? endif; ?>

					<dt class="payment_method">Payment method</dt>
					<dd class="payment_method"><?= $subscription["payment_method"] ? $subscription["payment_method"]["name"] : "N/A" ?></dd>
				<? endif; ?>

				<? if($subscription["order_id"]): ?>
					<dt class="payment_status">Payment status</dt>
					<dd class="payment_status<?= $subscription["order"]["payment_status"] < 2 ? " missing" : "" ?>"><?= $SC->payment_statuses[$subscription["order"]["payment_status"]] ?></dd>
				<? endif; ?>


				<? if($subscription["membership"]): ?>
					<dt class="membership">Membership</dt>
					<dd class="membership">This subscription is used for membership.</dd>
				<? endif; ?>

				</dl>

				<ul class="actions">
				<? if(!$subscription["membership"]): ?>
					<? if($subscription["item"]["subscription_method"]): ?>
					<?= $HTML->link("Edit", "/janitor/admin/user/subscription/edit/".$user_id."/".$subscription["id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
					<? endif; ?>

					<?= $JML->oneButtonForm("Delete", "/janitor/admin/profile/deleteSubscription/".$subscription["id"], array(
						"js" => true,
						"wrapper" => "li.delete",
						"static" => true
					)) ?>
				<? else: ?>
					<?= $HTML->link("View", "/janitor/admin/profile/membership/view", array("class" => "button", "wrapper" => "li.cancel")) ?>


				<? endif; ?>
				</ul>

			 </li>
		 	<? endforeach; ?>
		</ul>

		<? else: ?>
		<p>No subscriptions.</p>
		<? endif; ?>
	</div>

</div>