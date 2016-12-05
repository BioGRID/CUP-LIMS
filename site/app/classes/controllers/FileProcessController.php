<?php


namespace ORCA\app\classes\controllers;

/**
 * File Process Controller
 * This controller handles the processing of data files uploaded
 */
 
use ORCA\app\lib;
use ORCA\app\classes\models;

class FileProcessController extends lib\Controller {
	
	public function __construct( $twig ) {
		parent::__construct( $twig );
		
		$addonJS = array( );
		$addonJS[] = "orca-fileprocess.js";
		
		$addonCSS = array( );
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
	}
	
	/**
	 * Index
	 * Default layout for the main file processing page, called when no other actions
	 * are requested via the URL.
	 */
	
	public function Index( ) {
		
		lib\Session::canAccess( "curator" );
		$lookups = new models\Lookups( );
		
		// Fetch and Check Experiment ID
		$experimentID = 0;
		if( isset( $_GET['expID'] )) {
			if( is_numeric( $_GET['expID'] )) {
				$experimentID = $_GET['expID'];
			}
		}
		
		// See if we are redoing all files
		// or just parsing new ones
		$performFull = "false";
		if( isset( $_GET['full'] ) && strtolower($_GET['full']) == "true" ) {
			$performFull = "true";
		}
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"EXP_ID" => $experimentID,
			"PERFORM_FULL" => $performFull
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/FileProcess' />" );
		$this->headerParams->set( "TITLE", "File Process Experiment | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "fileProcess" . DS . "FileProcessIndex.tpl", $params, false );
				
	}

}

?>