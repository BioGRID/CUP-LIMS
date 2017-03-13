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
		$addonJS[] = "blocks/orca-dataTableBlock.js";
		
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
		
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "files/orca-files.js";
		
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$fileHandler = new models\FileHandler( );
		$buttons = $fileHandler->fetchFileToolbar( );
		
		$ids = array( );
		$isExp = true;
		$type = "exp";
		if( isset( $_GET['expIDs'] )) {
			$ids = explode( "|", $_GET['expIDs'] );
		} else if( isset( $_GET['fileIDs'] )) {
			$ids = explode( "|", $_GET['fileIDs'] );
			$isExp = false;
			$type = "file";
		}
		
		$includeBG = false;
		$incBGString = "false";
		if( isset( $_GET['includeBG'] ) && $_GET['includeBG'] == "true" ) {
			$includeBG = true;
			$incBGString = "true";
		} else if( !isset( $_GET['includeBG'] ) && !$isExp ) {
			$includeBG = true;
			$incBGString = "true";
		}
		
		$fileCount = $fileHandler->fetchFileCount( $ids, $includeBG, $isExp );
				 
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"TABLE_TITLE" => "Raw File List",
			"ROW_COUNT" => $fileCount,
			"WEB_NAME_ABBR" => CONFIG['WEB']['WEB_NAME_ABBR'],
			"SHOW_TOOLBAR" => false,
			"IDS" => implode( '|', $ids ),
			"INCLUDE_BG" => $incBGString,
			"TYPE" => $type,
			"BUTTONS" => $buttons
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Files' />" );
		$this->headerParams->set( "TITLE", "View Files" );
		
		$this->renderView( "files" . DS . "FilesIndex.tpl", $params, false );
				
	}
	
	/**
	 * View
	 * Main view for viewing an individual file. Presents a table file data 
	 * and presents options for downloading that data.
	 */
	
	public function View( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW FILES' ));
		
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "files/orca-rawreads.js";
		
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		// If we're not passed an ID, show 404
		if( !isset( $_GET['id'] ) || !is_numeric( $_GET['id'] )) {
			lib\Session::sendPageNotFound( );
		}
		
		$fileHandler = new models\FileHandler( );
		$fileInfo = $fileHandler->fetchFile( $_GET['id'] );
		
		if( !$fileInfo ) {
			lib\Session::sendPageNotFound( );
		}
		
		if( !$fileHandler->canAccess( $_GET['id'] ) ) {
			lib\Session::sendPermissionDenied( );
		}
		
		// If we got an id but it's invalid
		// show 404 error
		if( !$fileInfo ) {
			lib\Session::sendPageNotFound( );
		}
		
		$user = new lib\User( );
		$userInfo = $user->fetchUserDetails( $fileInfo->user_id );
		
		// See if a view already exists for this file
		$viewHandler = new models\ViewHandler( );
		$fileSet = array( );
		$fileSet[] = array( "fileID" => $fileInfo->file_id, "backgroundID" => "0" );
		$viewDetails = $viewHandler->addView( "File #" . $fileInfo->file_id . " Annotated Raw Data", "Raw Data Annotated with Group Info", 2, 2, $fileSet );
		$view = $viewHandler->fetchView( $viewDetails['ID'] );
		$viewHandler->updateLastViewed( $view->view_id );
		
		$rawCount = 0;
		if( $view->view_state != 'building' ) {
			// Fetch Raw Reads Info for Table
			$rawViewHandler = new models\RawAnnotatedViewHandler( $view->view_id );
			$rawCount = $rawViewHandler->fetchRowCount( $_GET['id'] );
		}
		
		// File Permissions Handling
		$canEdit = false;
		if( $fileInfo->user_id == $_SESSION[SESSION_NAME]['ID'] ) {
			$canEdit = true;
		}
		
		$isPrivate = false;
		if( $fileInfo->file_permission == "private" ) {
			$isPrivate = true;
		}
		
		// Get List of Groups
		$groupHandler = new models\GroupHandler( );
		$groups = $groupHandler->fetchGroups( );
		
		$selectedGroups = json_decode( $fileInfo->file_groups );
				 
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"FILE_ID" => $fileInfo->file_id,
			"FILE_NAME" => $fileInfo->file_name,
			"FILE_ADDEDDATE" => $fileInfo->file_addeddate,
			"FILE_STATE" => $fileInfo->file_state,
			"FILE_READTOTAL" => $fileInfo->file_readtotal,
			"USER_NAME" => $userInfo['NAME'],
			"FILE_SIZE" => $fileHandler->formatFileSize( $fileInfo->file_size ),
			"UPLOAD_PROCESSED_URL" => UPLOAD_PROCESSED_URL,
			"FILE_CODE" => $fileInfo->file_code,
			"TABLE_TITLE" => "Raw Data",
			"VIEW_ID" => $view->view_id,
			"VIEW_STATE" => $view->view_state,
			"ROW_COUNT" => $rawCount,
			"CAN_EDIT" => $canEdit,
			"IS_PRIVATE" => $isPrivate,
			"GROUPS" => $groups,
			"SELECTED_GROUPS" => $selectedGroups
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Files' />" );
		$this->headerParams->set( "TITLE", "View File " . $fileInfo->file_name );
		
		$this->renderView( "files" . DS . "FilesView.tpl", $params, false );
				
	}

}

?>