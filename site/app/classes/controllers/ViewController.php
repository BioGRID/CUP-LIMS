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
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW VIEW' ));
		
		if( isset( $_GET['viewID'] ) && is_numeric( $_GET['viewID'] )) {
			$viewHandler = new models\ViewHandler( );
			$view = $viewHandler->fetchView( $_GET['viewID'] );
			
			if( $view ) {
				// View Type 1 is a Matrix View
				if( $view->view_type_id == "1" ) {
					$this->Matrix( );
				}
			} else {
				// Can't find the view specified by this ID
				lib\Session::sendPageNotFound( );
			}
			
		} else {
			lib\Session::sendPageNotFound( );
		}
		
	}
	
	/**
	 * Create
	 * Tools to create a new view and select the appropriate
	 * configuration parameters to generate it
	 */
	 
	public function Create( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'CREATE VIEW' ));
		
		// If we're not passed an ID, show 404
		if( !isset( $_GET['expIDs'] )) {
			lib\Session::sendPageNotFound( );
		}
		
		$expIDs = explode( "|", $_GET['expIDs'] );
		
		// Add some Manage Permissions Specific JS
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "jquery.qtip.min.js";
		$addonJS[] = "formValidation/formValidation.min.js";
		$addonJS[] = "formValidation/bootstrap.min.js";
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "alertify.min.js";
		$addonJS[] = "orca-dataTableBlock.js";
		$addonJS[] = "view/orca-view-create.js";
		
		// Add some Manager Permissions Specific CSS
		$addonCSS = $this->headerParams->get( 'ADDON_CSS' );
		$addonCSS[] = "jquery.qtip.min.css";
		$addonCSS[] = "formValidation/formValidation.min.css";
		$addonCSS[] = "dataTables.bootstrap.css";
		$addonCSS[] = "alertify.min.css";
		$addonCSS[] = "alertify-bootstrap.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		// Fetch requirements for building the file listing
		$fileHandler = new models\FileHandler( );
		$fileCount = $fileHandler->fetchFileCount( $expIDs, false );
		$buttons = $fileHandler->fetchFileToolbarForAddView( $expIDs );
		$showFiles = true;
		
		// Fetch View Lists for Building Form
		$viewHandler = new models\ViewHandler( );
		$viewTypes = $viewHandler->fetchViewTypes( );
		$viewValues = $viewHandler->fetchViewValues( );
		
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"TABLE_TITLE" => "Select Files and Backgrounds for View",
			"ROW_COUNT" => $fileCount,
			"SHOW_TOOLBAR" => true,
			"INCLUDE_BG" => "false",
			"SHOW_FILES" => $showFiles,
			"VIEW_TYPES" => $viewTypes,
			"VIEW_VALUES" => $viewValues,
			"EXP_IDS" => implode( "|", $expIDs ),
			"BUTTONS" => $buttons
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/View/Create' />" );
		$this->headerParams->set( "TITLE", "Create View | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "view" . DS . "ViewCreate.tpl", $params, false );
		
	}
	
	/**
	 * Matrix
	 * This view generates a jquery datatable that presents an NxN Matrix
	 * where summary genes make up the Y axis and individual files make up the 
	 * X axis. Inside the units of the matrix are customizable pre-calculated values.
	 */
	
	public function Matrix( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW VIEW' ));
		
		// If we're not passed a numeric values and a set of file ids, show 404
		if( !isset( $_GET['viewID'] ) || !is_numeric( $_GET['viewID'] )) {
			lib\Session::sendPageNotFound( );
		}
		
		$viewHandler = new models\ViewHandler( );
		$view = $viewHandler->fetchView( $_GET['viewID'] );	
		
		// Add some Change Password Specific JS
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "alertify.min.js";
		$addonJS[] = "orca-dataTableBlock.js";
		
		if( $view->view_state == "building" ) {
			$addonJS[] = "view/orca-view.js";
		} else {
			$addonJS[] = "view/orca-view-matrix.js";
		}
		
		// Add some Change Password Specific CSS
		$addonCSS = $this->headerParams->get( 'ADDON_CSS' );
		$addonCSS[] = "dataTables.bootstrap.css";
		$addonCSS[] = "alertify.min.css";
		$addonCSS[] = "alertify-bootstrap.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"VIEW_ID" => $view->view_id,
			"VIEW_CODE" => $view->view_code,
			"VIEW_STATE" => $view->view_state
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Files' />" );
		$this->headerParams->set( "TITLE", "View Files | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "view" . DS . "ViewMatrix.tpl", $params, false );
				
	}

}

?>