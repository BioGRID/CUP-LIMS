<?php


namespace ORCA\app\classes\controllers;

/**
 * File Process Controller
 * This controller handles the processing of data files uploaded
 */
 
use ORCA\app\lib;
use ORCA\app\classes\models;

class FileProgressController extends lib\Controller {
	
	public function __construct( $twig ) {
		parent::__construct( $twig );
		
		$addonJS = array( );
		$addonJS[] = "fileProgress/orca-fileprogress.js";
		
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
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW FILE PROGRESS' ));
		$lookups = new models\Lookups( );
		
		// Fetch and Check File IDs
		$fileIDs = array( );
		if( isset( $_GET['files'] )) {
			$fileIDs = explode( "|", $_GET['files'] );
		}
		
		$fileHandler = new models\FileHandler( );
		$files = $fileHandler->fetchFilesByIDs( $fileIDs );
		$fileData = $fileHandler->fetchFormattedFileStates( $files );
		
		$totalComplete = $fileData['STATS']['ERROR'] + $fileData['STATS']['SUCCESS'];
		$progressPercent = ($totalComplete / $fileData['STATS']['TOTAL'])*100;
		
		$isRunning = "true";
		if( $totalComplete == $fileData['STATS']['TOTAL'] ) {
			$isRunning = "false";
		}

		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"FILE_INPROGRESS" => implode( "", $fileData['FILES']['INPROGRESS'] ),
			"FILE_COMPLETED" => implode( "", $fileData['FILES']['COMPLETED'] ),
			"FILE_QUEUED" => implode( "", $fileData['FILES']['QUEUED'] ),
			"QUEUED_FILES" => $fileData['STATS']['QUEUED'],
			"INPROGRESS_FILES" => $fileData['STATS']['INPROGRESS'],
			"ERROR_FILES" => $fileData['STATS']['ERROR'],
			"SUCCESS_FILES" => $fileData['STATS']['SUCCESS'],
			"TOTAL_FILES" => $fileData['STATS']['TOTAL'],
			"PROGRESS_PERCENT" => number_format( $progressPercent, 0 ),
			"COMPLETED_FILES" => $totalComplete,
			"EXPERIMENT_NAME" => "",
			"IS_RUNNING" => $isRunning
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/FileProcess' />" );
		$this->headerParams->set( "TITLE", "File Process Experiment" );
		
		$this->renderView( "fileProgress" . DS . "FileProgressIndex.tpl", $params, false );
				
	}
	
	/**
	 * Annotation
	 * Default layout for the annotation file processing page
	 */
	
	public function Annotation( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW UPLOAD ANNOTATION TOOL' ));
		$lookups = new models\Lookups( );
		
		// Fetch and Check File IDs
		$fileIDs = array( );
		if( isset( $_GET['files'] )) {
			$fileIDs = explode( "|", $_GET['files'] );
		}
		
		$fileHandler = new models\FileHandler( );
		$files = $fileHandler->fetchAnnotationFilesByIDs( $fileIDs );
		$fileData = $fileHandler->fetchFormattedFileStates( $files );
		
		$totalComplete = $fileData['STATS']['ERROR'] + $fileData['STATS']['SUCCESS'];
		$progressPercent = ($totalComplete / $fileData['STATS']['TOTAL'])*100;
		
		$isRunning = "true";
		if( $totalComplete == $fileData['STATS']['TOTAL'] ) {
			$isRunning = "false";
		}

		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"FILE_INPROGRESS" => implode( "", $fileData['FILES']['INPROGRESS'] ),
			"FILE_COMPLETED" => implode( "", $fileData['FILES']['COMPLETED'] ),
			"FILE_QUEUED" => implode( "", $fileData['FILES']['QUEUED'] ),
			"QUEUED_FILES" => $fileData['STATS']['QUEUED'],
			"INPROGRESS_FILES" => $fileData['STATS']['INPROGRESS'],
			"ERROR_FILES" => $fileData['STATS']['ERROR'],
			"SUCCESS_FILES" => $fileData['STATS']['SUCCESS'],
			"TOTAL_FILES" => $fileData['STATS']['TOTAL'],
			"PROGRESS_PERCENT" => number_format( $progressPercent, 0 ),
			"COMPLETED_FILES" => $totalComplete,
			"EXPERIMENT_NAME" => "",
			"IS_RUNNING" => $isRunning
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/FileProcess/Annotation' />" );
		$this->headerParams->set( "TITLE", "File Process Annotation" );
		
		$this->renderView( "fileProgress" . DS . "FileProgressAnnotation.tpl", $params, false );
				
	}

}

?>