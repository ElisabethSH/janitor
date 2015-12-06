<?php
global $action;
global $IC;
global $model;
global $itemtype;

// get wishlists
//$wishlists = $IC->getTags(array("context" => "wishlist", "order" => "value"));
$wishlists = $IC->getItems(array("itemtype" => "wishlist", "order" => "position ASC", "extend" => array("tags" => true)));

// get all wishes for complete overview
$wishes = $IC->getItems(array("itemtype" => $itemtype, "order" => "status DESC, wish.name ASC", "extend" => array("tags" => true, "mediae" => true)));

// reset "return to wishlist" state
session()->reset("return_to_wishlist");
?>
<div class="scene defaultList <?= $itemtype ?>List">
	<h1>Wishes and wishlists</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New wish")) ?>
		<?= $HTML->link("New wishlist", "/janitor/admin/wishlist/new", array("class" => "button primary", "wrapper" => "li.wishlist")) ?>
	</ul>

	<div class="wishlists">
		<h2>Wishlists</h2>
		<div class="all_items i:defaultList filters sortable"<?= $JML->jsData() ?>>
	<?		if($wishlists): ?>
			<ul class="items ">
	<?			foreach($wishlists as $wishlist):
					$tag_index = arrayKeyValue($wishlist["tags"], "context", "wishlist");
					if($tag_index !== false) {
						$tag = "wishlist:".addslashes($wishlist["tags"][$tag_index]["value"]);
						$wishlist_wishes = $IC->getItems(array("itemtype" => "wish", "tags" => $tag));
					}
					// create tag if wishlist doesnt have tag already
					else {
						$wishlist_wishes = array();
					}
	 ?>
				<li class="item draggable item_id:<?= $wishlist["id"] ?>">
					<div class="drag"></div>
					<h3><?= $wishlist["name"] ?> (<?= count($wishlist_wishes) ?> wishes)</h3>
					<ul class="actions">
						<?= $model->link("View", "/janitor/admin/wishlist/edit/".$wishlist["id"], array("class" => "button primary", "wrapper" => "li.edit")); ?>
						<?= $JML->deleteButton("Delete", "/janitor/admin/wishlist/delete/".$wishlist["id"], array("js" => true)); ?>
						<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/wishlist/status", $wishlist, array("js" => true)); ?>
					</ul>
				</li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No wishes.</p>
	<?		endif; ?>
		</div>
	</div>


	<div class="wishes">
		<h2>All wishes</h2>
		<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
	<?		if($wishes): ?>
			<ul class="items">
	<?			foreach($wishes as $item): ?>
				<li class="item image item_id:<?= $item["id"] ?> width:160<?= $JML->jsMedia($item) ?>">
					<h3><?= $item["name"] ?></h3>
					<dl>
						<dt class="reserved">Reserved</dt>
						<dd class="reserved"><?= $model->wish_reserved[$item["reserved"]] ?></dd>
					</dl>


					<?= $JML->tagList($item["tags"]) ?>

					<?= $JML->listActions($item) ?>
				 </li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No wishes.</p>
	<?		endif; ?>
		</div>
	</div>
</div>
