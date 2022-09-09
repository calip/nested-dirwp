<?php
	namespace ndtree;

	if ( ! defined( 'ABSPATH' ) ) exit;

	/**
	* 	Admin_Tree
	*
	*	Tree
	*
	* @author Alip Putra
	*
	*/
	class Admin_Tree
	{
		protected $tree;
		protected $firstCategory;

		public function __construct()
		{
			$this->tree = $this->getTree(0 , 0);
		} 

		public function display()
		{
			include(  __DIR__ . '/../../tpls/tree.php' );
		} 


		public function getTree( $catId , $level)
		{
			$childCats = array_filter(Tools_Categories::getChildrenCategories( $catId ));

			if( count($childCats)> 0 ){
				if( is_null(Admin_Screen::currentCatId()) ){
					Admin_Screen::currentCatId( $this->firstCategory->term_id );
				}
				$currendContextCatId = Admin_Screen::currentCatId();

				ob_start();
				include( __DIR__ . '/../../tpls/tree-part.php' );
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			}
		}
		
		public function getNbTrashPosts()
		{
			$acl = new Admin_ContentList();
		 	return count($acl->getTrashedElements());
		} 
		 

		public function firstCategory($firstCategory=null)
		{
			if( is_null( $firstCategory ) ){
				return $this->firstCategory;
			}
			$this->firstCategory = $firstCategory;
		} 
	}	