<?php


namespace ORCA\app\classes\controllers;

/**
 * Files Controller
 * This controller handles the processing of several different file listing 
 * specific views and options.
 */
 
use ORCA\app\lib;
use ORCA\app\classes\models;

class FilesController extends lib\Controller {
	
	public function __construct( $twig ) {
		parent::__construct( $twig );
		
		$addonJS = array( );
		$addonJS[] = "jquery.qtip.min.js";
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "alertify.min.js";
		$addonJS[] = "orca-dataTableBlock.js";
		$addonJS[] = "orca-files.js";
		
		$addonCSS = array( );
		$addonCSS[] = "jquery.qtip.min.css";
		$addonCSS[] = "dataTables.bootstrap.css";
		$addonCSS[] = "alertify.min.css";
		$addonCSS[] = "alertify-bootstrap.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
	}
	
	/**
	 * Index
	 * Default layout for the main files page, called when no other actions
	 * are requested via the URL.
	 */
	 
	public function Index( ) {
		$this->Listing( );
	}
	
	/**
	 * Listing
	 * Main view for the files page. Presents a table of files for a set of experiments
	 * with the ability to search, sort, browse results.
	 */
	
	public function Listing( ) {
		
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