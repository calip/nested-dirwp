<?php
	namespace ndtree;
	
	if ( ! defined( 'ABSPATH' ) ) exit;

	/**
	* 	Admin_ContentList
	*
	*
	* @author Alip Putra
	*
	*/
	class Admin_ContentList
	{
		const TRASH_ID = "trash";
		protected $catId;
		public function catId($catId=null)
		{
			if( is_null( $catId ) ){
				return $this->catId;
			}
			$this->catId = $catId;
		} 
		protected $catName;
		protected $onlyDirectChild = true;

		public function __construct()
		{
		}	

		public function display()
		{
			include( __DIR__ . '/../../tpls/list.php' );
		}
		public function displayCat( $catId , $catName)
		{
			$this->catId = $catId;
			$this->catName = $catName;
			
			
			$this->displayList();
		} 


		protected function displayList()
		{
			if( $this->catIsTrash() ){
				$this->elements = $this->getTrashedElements();
			}
			else{
				$this->elements = $this->getElements();
			}

			include( __DIR__ . '/../../tpls/list.php' );
		} 

		public function getElements()
		{
			// récupération des éléments: 
				$query_args = array(
					'posts_per_page' => -1,
					// 'author' => $this->sort_options->author,
					// 'orderby' => $this->sort_options->orderby,
					'post_status' => array('publish', 'pending', 'draft', 'private', 'future'/*, 'trash'*/),
					'cat' => $this->catId,
					// 'order' => $this->sort_options->order
				);
				$pages = new \WP_Query(apply_filters('ts-tree-filter', $query_args, 0));

			// tri : 
				$posts = array();;
				while ( $pages->have_posts() ){
					$pages->the_post();
					global $post;

					if( $this->mustShowPost( $post ) ){
						$posts[] = $post;
					}
				}

			return $posts;
		} 

		protected function mustShowPost($post)
		{
			global $wpdb;

			if( $this->onlyDirectChild() ){
				$query = "SELECT COUNT(*) FROM $wpdb->term_relationships AS tr 
									WHERE object_id = %s
										AND term_taxonomy_id= %s";
			
				$result = $wpdb->get_col( 
					//$wpdb->prepare( "SELECT term_id FROM " . $wpdb->term_taxonomy . " WHERE parent =".$catId , nul ));
					$wpdb->prepare( $query , $post->ID , $this->catId ));

				return intval($result[0]) > 0;
			}
			return true;
		} 

		public function catIsTrash( )
		{
			return $this->catId() == self::TRASH_ID;
		} 

		
		public function onlyDirectChild($onlyDirectChild=null)
		{
			if( is_null( $onlyDirectChild ) ){
				return $this->onlyDirectChild;
			}
			$this->onlyDirectChild = $onlyDirectChild;
		} 

		public function getTrashedElements()
		{
			$pages = new \WP_Query( array( 'post_status' => 'trash' ) );


			// tri : 
				$posts = array();
				while ( $pages->have_posts() ){
					$pages->the_post();
					global $post;
					$posts[] = $post;
				}
			return $posts;
		} 
	}