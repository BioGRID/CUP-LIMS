<?php

namespace ORCA\app\classes\models;

/**
 * Linkout Generator
 * This class is for building out linkouts to other websites
 * based on passed in parameters.
 */

use \PDO;
 
class LinkoutGenerator {
	
	/**
	 * Returns a website formatted linkout based on the
	 * passed in parameters
	 */

	public static function getLinkout( $type, $id ) {
		
		switch( strtolower( $type ) ) {
			
			case "biogrid" :
				return "https://thebiogrid.org/" . $id;
			
			default:
				return "";
		}

	}
	
}

?>