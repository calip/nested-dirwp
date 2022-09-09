<?php
	namespace ndtree;
	if ( ! defined( 'ABSPATH' ) ) exit;

	/**
	* 	TS_Tree
	*
	*	description
	*
	* @author Alip Putra
	*
	*/
	class TS_Tree
	{
		CONST MNAMESPACE = 'ndtree\\';

		public function __construct()
		{
			// autoload : 
				$this->initAutoload();

			// loadScreen : 
				$this->launch();
		} 
		
		/**
		 * Initalisation de l'environnement
		 *
		 * @return null
		 */
		protected function initAutoload()
		{
			if( !function_exists( 'd' ) ){
				require_once __DIR__.'/tools.php';
			}

			spl_autoload_register( array($this , 'loadClass') );
		} 

		/**
		 * Auto load des classes utiles
		 *
		 * @param  string $className Nom de la classe
		 *
		 * @return null
		 */
		protected function loadClass($className)
		{
			if( strpos( $className , self::MNAMESPACE ) !== 0  ){
				return;
			}
			$className = str_replace( self::MNAMESPACE , '', $className );
			$path = explode( "_" , $className );
			array_pop($path);
			$fullPath = __DIR__ . '/' . implode( "/" , $path) . "/"  . $className . ".class.php";
			if( file_exists($fullPath)){
				require_once $fullPath;
			}
		}

		/**
		 * Initialise le menu WP
		 *
		 * @return null
		 */
		protected function launch()
		{
			if( is_admin() ){
				$screen = new Admin_Screen();
			}
		}
	}