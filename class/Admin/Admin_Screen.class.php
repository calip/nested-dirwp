<?php
	namespace ndtree;

	if ( ! defined( 'ABSPATH' ) ) exit;

	/**
	* 	Admin_Screen
	*
	*	description
	*
	* @author Alip Putra
	*
	*/
	class Admin_Screen
	{
		const PAGE_NAME = "nd-tree-plugin";

		public function __construct()
		{
			 if( !session_id() )
    		    session_start();

    		// ajout de la translation : 
    			add_action('plugins_loaded', array($this , 'load_textdomain') );
    		
			// ajout du menu :
		 		add_action( 'admin_menu', array($this , 'admin_menu') );

		 	// ajout de l'action
		 		add_action( 'wp_ajax_load_categorie_content', array($this , 'loadCategorieContent') );
		 		add_action( 'wp_ajax_delete_post', array($this , 'deletePost') );
		 		add_action( 'wp_ajax_delete_post_from_cat', array($this , 'deletePostFromCat') );
		 		add_action( 'wp_ajax_delete_post_permanently', array($this , 'deletePostPermanently') );
		 		add_action( 'wp_ajax_draft_post', array($this , 'draftPost') );
		 		add_action( 'wp_ajax_pubish_post', array($this , 'publishPost') );
		 		add_action( 'wp_ajax_get_tree', array($this , 'getTree') );
		 		add_action( 'wp_ajax_add_categorie', array($this , 'addCategorie') );
		 		add_action( 'wp_ajax_delete_categorie', array($this , 'deleteCategorie') );
		 		add_action( 'wp_ajax_update_categorie_tree', array($this , 'updateCategorieTree') );
		 		add_action( 'wp_ajax_get_category_data', array($this , 'getCategoryData') );
		 		add_action( 'wp_ajax_edit_categorie', array($this , 'editCategory') );

		 	if( $_GET["page"] != self::PAGE_NAME ){
		 		$this->loadHideScripts();
		 	} 
		 		
		} 

		public function loadHideScripts() 
		{
	    	wp_register_style( 'custom_wp_admin_css_hide_bars', plugin_dir_url( __FILE__ ) . '/../../../css/hide_bars.css', false, '1.0.0' );
			wp_enqueue_style( 'custom_wp_admin_css_hide_bars' );			
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'my_custom_script', plugin_dir_url( __FILE__ ) . '/../../../js/hide_scripts.js' );
		}

		/**
		 * [admin_menu description]
		 *
		 * @return [type] [description]
		 */
		public function admin_menu()
		{
			$title = __('Tree' , 'nd-tree-plugin');
			add_posts_page( $title , $title, 'manage_categories', self::PAGE_NAME, array($this,'content_page'));
		} 

		public function load_textdomain()
		{
			load_plugin_textdomain( self::PAGE_NAME , false, dirname( plugin_basename(__DIR__.'/../../../') ) . '/lang/' );
		}

		public function content_page()
		{
			// scripts : 
				wp_enqueue_script('jquery');
				// wp_enqueue_script('jqueryUi' , plugins_url('/../../js/jquery.ui.js' , __FILE__));
				wp_enqueue_script("jquery-ui-core");
				wp_enqueue_script("jquery-ui-sortable");
				wp_enqueue_script( 'nestedSortable', plugin_dir_url( __FILE__ ) . '/../../../js/nestedSortable.js' );
				wp_enqueue_script( 'my_custom_script', plugin_dir_url( __FILE__ ) . '/../../../js/scripts.js' );

			// styles
				wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . '/../../../css/style.css', false, '1.0.0' );
				wp_enqueue_style( 'custom_wp_admin_css' );

				echo "<div id='ts-tree'>";

			// tree
				$tree = new Admin_Tree();
				$tree->display();

			//	content zone
				$list = new Admin_ContentList();
				$list->display();

			// content edit
				echo '<div id="edit-content"></div>';

				echo "</div>";
		} 

		public function loadCategorieContent()
		{
			$data = !empty($_POST['catId']) ? $_POST : $_GET;
			self::currentCatId( $data['catId']);
			$list = new Admin_ContentList();
			$list->displayCat( $data['catId'] , $data['catName'] );
			exit;
		}

		static public function currentCatId($currentCatId = null)
		{
			if(is_null($currentCatId))
				return $_SESSION[ self::PAGE_NAME ]["currentCatId"];
			else
				$_SESSION[ self::PAGE_NAME ]["currentCatId"] = $currentCatId;
		}

		public function deletePost()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;
			$postId = $data['postId'];
			wp_delete_post( $postId );
		} 

		public function deletePostPermanently()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;
			$postId = $data['postId'];
			wp_delete_post( $postId );
		}

		public function draftPost()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;
			wp_update_post( array('ID'=>$data['postId'] , 'post_status'=>'draft') );
		}

		public function publishPost()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;
			wp_update_post( array('ID'=>$data['postId'] , 'post_status'=>'publish') );
		}

		public function deletePostFromCat()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;
			$currentCats = array_flip(wp_get_post_cats( null, $data['postId'] ));
			unset( $currentCats[ $data['catId'] ] );
			wp_set_post_cats( null , $data['postId'] , array_flip($currentCats));
		} 

		public function getTree()
		{
			$tree = new Admin_Tree();
			$tree->display();
		} 

		public function addCategorie()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;

			$catData = array('cat_name' => $data['newCatName'], 'category_description' => $data['newCatDesc'], 'category_nicename' => $data['newCatId'], 'category_parent' => $data['parentCatId']);
			wp_insert_category($catData);
		} 

		public function deleteCategorie()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;
			wp_delete_category( $data['catId'] );
		} 

		public function updateCategorieTree()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;
			Tools_Categories::initCategoriesTree( $data['tree'] );
		}

		public function getCategoryData()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;
			echo json_encode(Tools_Categories::getCategoryDataFromCategoryId($data['catId']));exit;
		}

		public function editCategory()
		{
			$data = !empty($_POST['action']) ? $_POST : $_GET;

			$catData = array('cat_ID'=>$data['catId'] , 'cat_name' => $data['catName'], 'category_description' => $data['catDesc'], 'category_nicename' => $data['catSlug']);
			wp_update_category( $catData );
		}

	}