<?
	namespace ndtree;
	
	if ( ! defined( 'ABSPATH' ) ) exit;

	/**
	 * Tools_Categories
	 *	
	 * description
	 *
	 * @author Alip Putra
	 *
	 * @date YYYY-mm-dd HH:mm
	 */
	class Tools_Categories
	{
		function __construct(){}

		static public function getChildrenCategories( $catId )
		{
			global $wpdb;
			
			$query = "SELECT t.term_id FROM $wpdb->term_taxonomy AS tt 
										LEFT JOIN $wpdb->terms as t ON t.term_id=tt.term_id 
									WHERE parent = %s AND taxonomy = 'staff_category'
									ORDER BY term_order ASC";
									
			$result = $wpdb->get_col( 
					$wpdb->prepare( $query , $catId ));

			$categories = array();
			foreach( $result as $childrenId ){
				$categories[] = self::getCategoryDataFromCategoryId( $childrenId );
			}
			return $categories;
		} 

		static public function getCategoryDataFromCategoryId( $catId )
		{
			return get_term($catId, 'staff_category');
			// return get_category( $catId );
		} 
		static public function getCategoryDataFromCategorySlug( $slug )
		{
			return get_category_by_slug( $slug );
		} 
		static public function getPostsForCategoryId($catId)
		{
			$posts = query_posts( array('cat' => $catId) );
			// self::initPosts( $posts );
			// d( $catId );
			return $posts;
		}

		static public function getPostsForCategoryName($catName)
		{
			$posts = query_posts( array('category_name' => $catName) );
			// self::initPosts( $posts );
			return $posts;
		}

		static public function initCategoriesTree($data , $parent = 0 , $order=0)
		{
			if( $data['id'] ){
				if( is_null($parent) ){
					$parent = 0;
				}
				if( is_numeric($parent) ){
					wp_update_term($data['id'], 'category', array(
						'parent' => $parent
					));
					self::setCatOrder( $data['id'] , $order );
				}
			}
			if( $data['children'] ){
				$order=0;
				foreach( $data['children']  as $childElement ){
					self::initCategoriesTree( $childElement , $data['id'] , $order);
					$order++;
				}
			}
		}

		static public function setCatOrder($catId , $order)
		{
			global $wpdb;

			$query = "SELECT t.term_id FROM $wpdb->term_taxonomy AS tt 
										LEFT JOIN $wpdb->terms as t ON t.term_id=tt.term_id 
									WHERE parent = %s AND taxonomy = 'staff_category'
									ORDER BY term_order ASC";
			// $query = $wpdb->update(
			// 		$wpdb->terms,
			// 		array( 'term_order'=> $order ),
			// 		'term_id = ' . $catId
			// 	);
			
			$query  = "UPDATE $wpdb->terms SET term_order=" . $order . " 
									WHERE term_id = %s";
			$result = $wpdb->query( 
					$wpdb->prepare( $query , $catId ));
		}
	}