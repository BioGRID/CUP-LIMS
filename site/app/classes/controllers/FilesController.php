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
		$this->headerParams->set( "TITLE", "View Files | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "files" . DS . "FilesIndex.tpl", $params, false );
				
	}
	
	/**
	 * View
	 * Main view for viewing an individual file. Presents a table file data 
	 * and presents options for downloading that data.
	 */
	
	public function View( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW FILES' ));
		
		// If we're not passed an ID, show 404
		if( !isset( $_GET['id'] ) || !is_numeric( $_GET['id'] )) {
			lib\Session::sendPageNotFound( );
		}
		
		$fileHandler = new models\FileHandler( );
		$fileInfo = $fileHandler->fetchFile( $_GET['id'] );
		
		// If we got an id but it's invalid
		// show 404 error
		if( !$fileInfo ) {
			lib\Session::sendPageNotFound( );
		}
		
		$user = new lib\User( );
		$userInfo = $user->fetchUserDetails( $fileInfo->user_id );
				 
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"FILE_NAME" => $fileInfo->file_name,
			"FILE_ADDEDDATE" => $fileInfo->file_addeddate,
			"FILE_STATE" => $fileInfo->file_state,
			"FILE_READTOTAL" => $fileInfo->file_readtotal,
			"USER_NAME" => $userInfo['NAME'],
			"FILE_SIZE" => $fileHandler->formatFileSize( $fileInfo->file_size ),
			"EXPERIMENT_ID" => $fileInfo->experiment_id,
			"EXPERIMENT_NAME" => $fileInfo->experiment_name,
			"UPLOAD_PROCESSED_URL" => UPLOAD_PROCESSED_URL,
			"EXPERIMENT_CODE" => $fileInfo->experiment_code,
			"TABLE_TITLE" => "Raw Data"
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Files' />" );
		$this->headerParams->set( "TITLE", "View File | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "files" . DS . "FilesView.tpl", $params, false );
				
	}

}

?>