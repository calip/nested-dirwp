<? namespace ndtree;
if ( ! defined( 'ABSPATH' ) ) exit;?>
<ol class="sortable">
	<?
		foreach( $childCats as $childCat ){
			$acl = new Admin_ContentList();
			$acl->catId( $childCat->term_id );
			$nbElements = count($acl->getElements());
			$currentClass = $currendContextCatId == $childCat->term_id ? 'current' : '';
	?>
	<li>
		<div class="element">
			<div class="btn-deploy">
				<span class="grow">+</span>
				<span class="shrink">-</span>
			</div>
			<div id="<? echo $childCat->term_id;?>" class="label <? echo $currentClass?>">
				<span class="title"><?echo $childCat->name; ?> ( <?echo $nbElements;?> ) </span>
				<div class="actions">
					<span class="add-cat dashicons dashicons-plus"></span>
					<span class="edit-cat dashicons dashicons-edit"></span>
					<span class="delete-cat dashicons dashicons-trash"></span>
				</div>
			</div>
		</div>
		<? echo $this->getTree( $childCat->term_id , $level+1); ?>
	</li>
	<?
		}
		if( $level == 0 ){
			$currentClass = $currendContextCatId == Admin_ContentList::TRASH_ID ? 'current' : '';
			?>
	<li>
		<div id="<?echo Admin_ContentList::TRASH_ID;?>" class="label <?echo $currentClass;?>">Trash (<? echo $this->getNbTrashPosts();?>) </div>
	</li>
<?
	}
	?>
</ol>