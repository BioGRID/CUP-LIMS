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
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW VIEWS' ));
		
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
			$this->Listing( );
		}
		
	}
	
	/**
	 * Listing
	 * Generate a listing of views that are available for browsing
	 */
	 
	public function Listing( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW VIEWS' ));
		
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "jquery.qtip.min.js";
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "alertify.min.js";
		$addonJS[] = "blocks/orca-dataTableBlock.js";
		$addonJS[] = "view/orca-view-listing.js";
		
		$addonCSS = $this->headerParams->get( 'ADDON_CSS' );
		$addonCSS[] = "jquery.qtip.min.css";
		$addonCSS[] = "dataTables.bootstrap.css";
		$addonCSS[] = "alertify.min.css";
		$addonCSS[] = "alertify-bootstrap.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$canCreateView = lib\Session::validateCredentials( lib\Session::getPermission( 'CREATE VIEWS' ));
		
		$expHandler = new models\ViewHandler( );
		$expCount = $expHandler->fetchViewCount( );
		$buttons = $expHandler->fetchViewToolbar( );
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"TABLE_TITLE" => "Custom Views",
			"ROW_COUNT" => $expCount,
			"WEB_NAME_ABBR" => CONFIG['WEB']['WEB_NAME_ABBR'],
			"VIEW_CREATE_VALID" => $canCreateView,
			"SHOW_TOOLBAR" => true,
			"BUTTONS" => $buttons
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/View' />" );
		$this->headerParams->set( "TITLE", "View Listing | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "view" . DS . "ViewListing.tpl", $params, false );
		
	}
	
	/**
	 * Create
	 * Tools to create a new view and select the appropriate
	 * configuration parameters to generate it
	 */
	 
	public function Create( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'CREATE VIEWS' ));
		
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
		$addonJS[] = "blocks/orca-dataTableBlock.js";
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
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW VIEWS' ));
		
		// If we're not passed a numeric values and a set of file ids, show 404
		if( !isset( $_GET['viewID'] ) || !is_numeric( $_GET['viewID'] )) {
			lib\Session::sendPageNotFound( );
		}
		
		$viewHandler = new models\ViewHandler( );
		$view = $viewHandler->fetchView( $_GET['viewID'] );	
		$viewHandler->updateLastViewed( $_GET['viewID'] );
		$viewIcon = $viewHandler->fetchViewTypeIcon( $view->view_type_id );
		
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "alertify.min.js";
		
		$addonCSS = $this->headerParams->get( 'ADDON_CSS' );
		$addonCSS[] = "alertify.min.css";
		$addonCSS[] = "alertify-bootstrap.min.css";
		
		$rowCount = 0;
		$viewTpl = "";
		$params = array( );
		if( $view->view_state == "building" ) {
			$addonJS[] = "view/orca-view.js";
			$viewTpl = "ViewBuilding.tpl";
			
			$params = array( 
				"VIEW_ID" => $view->view_id,
				"VIEW_CODE" => $view->view_code,
				"VIEW_STATE" => $view->view_state,
			);
			
		} else {
			
			// Add some matrix view Specific JS
			$addonJS[] = "jquery.qtip.min.js";
			$addonJS[] = "jquery.dataTables.js";
			$addonJS[] = "dataTables.bootstrap.js";
			$addonJS[] = "alertify.min.js";
			$addonJS[] = "blocks/orca-dataTableBlock.js";
			$addonJS[] = "view/orca-view-matrix.js";
			
			// Add some matrix view Specific CSS
			$addonCSS[] = "jquery.qtip.min.css";
			$addonCSS[] = "dataTables.bootstrap.css";
			$addonCSS[] = "alertify.min.css";
			$addonCSS[] = "alertify-bootstrap.min.css";
			
			// Get style if it's passed to us as a parameter
			$viewStyle = 3;
			if( isset( $_GET['style'] ) && ($_GET['style'] == 2 || $_GET['style'] == 3)) {
				$viewStyle = $_GET['style'];
			}
			
			$matrixHandler = new models\MatrixViewHandler( $_GET['viewID'] );
			$rowCount = $matrixHandler->fetchRowCount( );
			$toolbarButtons = $matrixHandler->fetchToolbar( $viewStyle );
			$colLegend = $matrixHandler->fetchColumnLegend( );
			$viewTpl = "ViewMatrix.tpl";
			
			$user = new lib\User( );
			$userInfo = $user->fetchUserDetails( $view->user_id );
			
			$params = array(
				"WEB_URL" => WEB_URL,
				"IMG_URL" => IMG_URL,
				"TABLE_TITLE" => "Matrix Dataset",
				"ROW_COUNT" => $rowCount,
				"DATATABLE_CLASS" => "matrixTable",
				"SHOW_TOOLBAR" => true,
				"HIDE_CHECK_ALL" => true,
				"COL_LEGEND" => $colLegend,
				"BUTTONS" => $toolbarButtons,
				"VIEW_NAME" => $view->view_title,
				"VIEW_DESC" => $view->view_desc,
				"VIEW_TYPE" => $viewHandler->fetchViewTypeName( $view->view_type_id ),
				"VIEW_VALUE" => $viewHandler->fetchViewValueName( $view->view_value_id ),
				"USER_NAME" => $userInfo['NAME'],
				"VIEW_ADDEDDATE" => $view->view_addeddate,
				"VIEW_ID" => $view->view_id,
				"VIEW_CODE" => $view->view_code,
				"VIEW_STATE" => $view->view_state,
				"VIEW_STYLE" => $viewStyle,
				"VIEW_ICON" => $viewIcon
			);
			
		}
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Files' />" );
		$this->headerParams->set( "TITLE", "View Files | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "view" . DS . $viewTpl, $params, false );
				
	}
	
	/**
	 * Download
	 * This view generates a file that presents the data stored in a spcific view
	 */
	
	public function Download( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'DOWNLOAD VIEWS' ));
		
		// If we're not passed a numeric values and a set of file ids, show 404
		if( !isset( $_GET['viewID'] ) || !is_numeric( $_GET['viewID'] )) {
			lib\Session::sendPageNotFound( );
		}
		
		$viewHandler = new models\ViewHandler( );
		$view = $viewHandler->fetchView( $_GET['viewID'] );	
		
		if( $view ) {
			if( $view->view_status == 'active' && $view->view_state == 'complete' ) {
				// Build Download
				$viewHandler->updateLastViewed( $_GET['viewID'] );
				
				header( "Content-type: plain/text" );
				header( "Content-disposition: inline; filename=" . $view->view_title . ".txt" );
				
				$downloadsHandler = new models\ViewDownloadsHandler( $view->view_id );
				$downloadsHandler->outputRows( );
				
			} else {
				lib\Session::sendPageNotFound( );
			}
		} else {
			echo "HERE";
			lib\Session::sendPageNotFound( );
		}

				
	}

}

?>