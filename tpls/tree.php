<?if ( ! defined( 'ABSPATH' ) ) exit;?>
<div>
	<div class="tree">
		<div class="loader">
			<div></div>
		</div>
		<h3>
			<?_e('Categories' , 'nd-tree-plugin');?>
			<span id="add-top-cat" class="dashicons dashicons-plus" title="<?_e('Add a category' , 'nd-tree-plugin');?>"></span>
		</h3>

		<? echo $this->tree; ?>
	</div>

	<div id="add-categorie-form" class="add-categorie">
		<div class="loader">
			<div></div>
		</div>
		<div class="content">
			<h3 class="add_cat_title"><?_e('New category' , 'nd-tree-plugin');?></h3>
			<h3 class="edit_cat_title"><?_e('Edit category' , 'nd-tree-plugin');?></h3>

			<div>
				<label><?_e('Name' , 'nd-tree-plugin');?></label>
				<input name="cat-name" type="text"/>
			</div>
			<div>
				<label><?_e('Description' , 'nd-tree-plugin');?></label>
				<textarea name="cat-desc">
				</textarea>
			</div>
			<div>
				<label><?_e('Identifier' , 'nd-tree-plugin');?></label>
				<input name="cat-identifiant" type="text"/>
			</div>

			<div class="submit">
				<input type="button" value="&nbsp;" />
			</div>
			<div class="close dashicons dashicons-no-alt" title="<?_e('Close' , 'nd-tree-plugin');?>"></div>
		</div>
	</div>
</div>

<div id="hiddenInfos">
	<span id="deleteCatConfirmText"><?_e('Do you really want to delete this category ?' , 'nd-tree-plugin');?></span>
</div>