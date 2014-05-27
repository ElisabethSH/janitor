<?php
global $action;
global $model;

$item = $model->getUsers(array("user_id" => $action[1]));
$user_groups_options = $model->toOptions($model->getUserGroups(), "id", "user_group");

// TODO: Create global function for languages (don't know if it is supposed to be in Page, Shop or User)
$query = new Query();
$query->sql("SELECT * FROM ".UT_LANGUAGES);
$languages = $query->results();
$language_options = $model->toOptions($languages, "id", "name");

// get usernames
$mobile = $model->getUsernames(array("user_id" => $item["id"], "type" => "mobile"));
$email = $model->getUsernames(array("user_id" => $item["id"], "type" => "email"));

// get addresses
$addresses = $model->getAddresses(array("user_id" => $item["id"]));

// get newsletters
$newsletters = $model->getNewsletters(array("user_id" => $item["id"]));

?>
<div class="scene defaultEdit userEdit">
	<h1>Edit user</h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/user/list/<?= $item["user_group_id"] ?>" class="button">All users</a></li>
		<li class="delete">
			<form action="/admin/user/delete/<?= $item["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
				<input type="submit" value="Delete" class="button delete" />
			</form>
		</li>
	</ul>


	<ul class="views">
		<li class="profile selected"><a href="/admin/user/<?= $item["id"] ?>">Profile</a></li>
		<li class="content"><a href="/admin/user/content/<?= $item["id"] ?>">Content and orders</a></li>
	</ul>

	<div class="status">
		<ul class="actions">
			<li class="status <?= ($item["status"] == 1 ? "enabled" : "disabled") ?>">
				<form action="/admin/user/disable/<?= $item["id"] ?>" class="disable i:formDefaultStatus" method="post" enctype="multipart/form-data">
					<h3>Enabled</h3>
					<input type="submit" value="Disable" class="button status disable" />
				</form>
				<form action="/admin/user/enable/<?= $item["id"] ?>" class="enable i:formDefaultStatus" method="post" enctype="multipart/form-data">
					<h3>Disabled</h3>
					<input type="submit" value="Enable" class="button status enable" />
				</form>
			</li>
		</ul>
	</div>

	<div class="item i:defaultEdit">
		<form action="/admin/user/update/<?= $item["id"] ?>" class="labelstyle:inject" method="post" enctype="multipart/form-data">
			<h3>Name, language and user group</h3>
			<fieldset>
				<?= $model->input("nickname", array("value" => $item["nickname"])) ?>
				<?= $model->input("firstname", array("value" => $item["firstname"])) ?>
				<?= $model->input("lastname", array("value" => $item["lastname"])) ?>
				<?= $model->input("language", array("type" => "select", "value" => $item["language"], "options" => $language_options)) ?>
				<?= $model->input("user_group_id", array("type" => "select", "value" => $item["user_group_id"], "options" => $user_groups_options)) ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/user/list/<?= $item["user_group_id"] ?>" class="button">Back</a></li>
				<li class="save"><input type="submit" value="Update" class="button primary" /></li>
			</ul>
		</form>
	</div>

	<h2>Email and Mobile number</h2>
	<div class="usernames i:usernames">
		<p>Your email and mobilenumber are your unique usernames.</p> 

		<form action="/admin/user/updateUsernames/<?= $item["id"] ?>" class="labelstyle:inject" method="post" enctype="multipart/form-data">
			<fieldset>
				<?= $model->input("email", array("value" => stringOr($email))) ?>
				<?= $model->input("mobile", array("value" => stringOr($mobile))) ?>
			</fieldset>
			<ul class="actions">
				<li class="save"><input type="submit" value="Update usernames" class="button primary" /></li>
			</ul>
		</form>
	</div>

	<h2>Password</h2>
	<div class="password i:password">
		<p>Type your new password to set or update your password</p>

		<form action="/admin/user/setPassword/<?= $item["id"] ?>" class="" method="post" enctype="multipart/form-data">
			<fieldset>
				<?= $model->input("password") ?>
			</fieldset>
			<ul class="actions">
				<li class="save"><input type="submit" value="Update password" class="button primary" /></li>
			</ul>
		</form>
	</div>

	<h2>Addresses</h2>
	<div class="addresses">
<? if($addresses): ?>
		<p>These addresses are associated with your account</p>

		<ul class="addresses">
<?			foreach($addresses as $address): ?>
			<li>
				<h3 class="address_label"><?= $address["address_label"] ?></h3>
				<div class="address_name"><?= $address["address_name"] ?></div>
				<?= $address["att"] ? ('<div class="att">Att: ' . $address["att"] . '</div>') : '' ?>
				<div class="address1"><?= $address["address1"] ?></div>
				<?= $address["address2"] ? ('<div class="address2">' . $address["address2"] . '</div>') : '' ?>
				<div class="postal_city">
					<span class="postal"><?= $address["postal"] ?></span>
					<span class="city"><?= $address["city"] ?></span>
				</div>
				<?= $address["state"] ? ('<div class="state">' . $address["state"] . '</div>') : '' ?>
				<div class="country"><?= $address["country"] ?></div>

				<ul class="actions">
					<li class="edit"><a href="/admin/user/edit_address/" class="button">Edit</a></li>
				</ul>
			</li>
<?			endforeach; ?>
		</ul>
<? else: ?>
		<p>You don't have any addresses associated with your account</p>
<? endif; ?>

		<ul class="actions">
			<li class="add"><a href="/admin/user/new_address/<?= $item["id"] ?>" class="button primary">Add new address</a></li>
		</ul>
	</div>

	<h2>Newsletters</h2>
	<div class="newsletters">
<? if($newsletters): ?>
		<p>You are subscribed to these newsletters</p>

		<ul class="newsletters i:userNewsletters">
<?			foreach($newsletters as $newsletter): ?>
			<li><?= $newsletter["newsletter"] ?></li>
<?			endforeach; ?>
		</ul>
<? else: ?>
	<p>You don't have any newsletters subscription for your account</p>
<? endif; ?>
	</div>

</div>