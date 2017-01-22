<?php


namespace ORCA\app\classes\controllers;

/**
 * View Controller
 * This controller handles the processing of several different view layouts 
 * for specific files and formats.
 */
 
use ORCA\app\lib;
use ORCA\app\classes\models;

class ViewController extends lib\Controller {
	
	public function __construct( $twig ) {
		parent::__construct( $twig );
		
		$addonJS = array( );
		$addonCSS = array( );
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
	}
	
	/**
	 * Index
	 * Default layout for the main views page, called when no other actions
	 * are requested via the URL.
	 */
	 
	public function Index( ) {
		
		if( isset( $_GET['type'] ) && isset( $_GET['values'] )) {
			if( $_GET['type'] == "1" ) {
				$this->Matrix( );
			}
		} else {
			lib\Session::sendPageNotFound( );
		}
		
	}
	
	/**
	 * Matrix
	 * This view generates a jquery datatable that presents an NxN Matrix
	 * where summary genes make up the Y axis and individual files make up the 
	 * X axis. Inside the units of the matrix are customizable pre-calculated values.
	 */
	
	public function Matrix( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW FILES' ));
		
		$fileHandler = new models\FileHandler( );
		$buttons = $fileHandler->fetchFileToolbar( );
		
		$expIDs = array( );
		if( isset( $_GET['expIDs'] )) {
			$expIDs = explode( "|", $_GET['expIDs'] );
		}
		
		$includeBG = false;
		$incBGString = "false";
		if( isset( $_GET['includeBG'] ) && $_GET['includeBG'] == "true" ) {
			$includeBG = true;
			$incBGString = "true";
		}
		
		$fileCount = $fileHandler->fetchFileCount( $expIDs, $includeBG );
				 
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"TABLE_TITLE" => "Raw File List",
			"ROW_COUNT" => $fileCount,
			"WEB_NAME_ABBR" => CONFIG['WEB']['WEB_NAME_ABBR'],
			"SHOW_TOOLBAR" => true,
			"EXP_IDS" => implode( '|', $expIDs ),
			"INCLUDE_BG" => $incBGString,
			"BUTTONS" => $buttons
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Files' />" );
		$this->headerParams->set( "TITLE", "View Files | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "files" . DS . "FilesIndex.tpl", $params, false );
				
	}

}

?>