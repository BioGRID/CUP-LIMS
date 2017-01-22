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
		
		if( isset( $_GET['type'] )) {
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
		
		lib\Session::canAccess( lib\Session::getPermission( 'CREATE VIEW' ));
		
		// If we're not passed a numeric values and a set of file ids, show 404
		if( !isset( $_GET['fileIDs'] ) || !isset( $_GET['values'] ) || !is_numeric( $_GET['values'] )) {
			lib\Session::sendPageNotFound( );
		}
		
		// Add some Change Password Specific JS
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "alertify.min.js";
		$addonJS[] = "orca-dataTableBlock.js";
		$addonJS[] = "view/orca-view-matrix.js";
		
		// Add some Change Password Specific CSS
		$addonCSS = $this->headerParams->get( 'ADDON_CSS' );
		$addonCSS[] = "dataTables.bootstrap.css";
		$addonCSS[] = "alertify.min.css";
		$addonCSS[] = "alertify-bootstrap.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$fileIDs = explode( "|", $_GET['fileIDs'] );
		$values = $_GET['values'];
		
		$viewHandler = new models\ViewHandler( );
		$viewInfo = $viewHandler->addView( $fileIDs, "1", $values );
				 
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"VIEW_ID" => $viewInfo['ID'],
			"VIEW_CODE" => $viewInfo['CODE'],
			"PROGRESS_TITLE" => "Matrix View Generating...",
			"PROGRESS_BODY" => "Your selected view is being generated. This process can sometimes take up to 5 minutes, based on complexity, so please be patient and <strong>do not leave this page</strong>. This progress indicator will be removed upon completed generation of the view."
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Files' />" );
		$this->headerParams->set( "TITLE", "View Files | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "view" . DS . "ViewMatrix.tpl", $params, false );
				
	}

}

?>