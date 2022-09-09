<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**********************************************
**				DEBUG						**
**********************************************/
	function d( $var , $stop=false , $backtrace=false)
	{
		ob_start();
		var_dump( $var );
		$content = ob_get_contents();
		ob_end_clean();
		echo "<pre>".htmlentities(($content))."</pre>";
		if( $backtrace ){
			d( debug_backtrace() );
		}
		if( $stop ){
			exit;
		}
		
	}

	function m( $stop=false )
	{
		$backtrace = debug_backtrace();
		$backtrace = $backtrace[1]['class'].'::'.$backtrace[1]['function'];
		d( $backtrace , $stop );
	}
