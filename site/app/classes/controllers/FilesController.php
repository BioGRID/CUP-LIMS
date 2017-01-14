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
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "alertify.min.js";
		$addonJS[] = "orca-dataTableBlock.js";
		$addonJS[] = "orca-files.js";
		
		$addonCSS = array( );
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
		$this->View( );
	}
	
	/**
	 * View
	 * Main view for the files page. Presents a table of files for a set of experiments
	 * with the ability to search, sort, browse results.
	 */
	
	public function View( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW FILES' ));
		
		$fileHandler = new models\FileHandler( );
		$fileCount = $fileHandler->fetchFileCount( );
		$buttons = array( );//$expHandler->fetchExperimentToolbar( );
		
		if( isset( $_GET['expIDs'] )) {
			$expIDs = explode( "|", $_GET['expIDs'] );
		}
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"TABLE_TITLE" => "File List",
			"ROW_COUNT" => $fileCount,
			"WEB_NAME_ABBR" => CONFIG['WEB']['WEB_NAME_ABBR'],
			"SHOW_TOOLBAR" => false,
			"BUTTONS" => $buttons
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Files' />" );
		$this->headerParams->set( "TITLE", "View Files | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "files" . DS . "FilesIndex.tpl", $params, false );
				
	}

}

?>