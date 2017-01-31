<?php


namespace ORCA\app\classes\controllers;

/**
 * Experiment Controller
 * This controller handles the processing of several different experiment 
 * specific views and options.
 */
 
use ORCA\app\lib;
use ORCA\app\classes\models;

class ExperimentController extends lib\Controller {
	
	public function __construct( $twig ) {
		parent::__construct( $twig );
		
		$addonJS = array( );
		$addonJS[] = "jquery.qtip.min.js";
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "alertify.min.js";
		$addonJS[] = "orca-dataTableBlock.js";
		
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
	 * Default layout for the main experiment page, called when no other actions
	 * are requested via the URL.
	 */
	 
	public function Index( ) {
		$this->Listing( );
	}
	
	/**
	 * Listing
	 * Main view for the experiment page. Presents a table of uploaded experiments
	 * with the ability to search, sort, browse results.
	 */
	
	public function Listing( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW EXPERIMENTS' ));
		
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "orca-experiment.js";
		
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$isMember = lib\Session::validateCredentials( lib\Session::getPermission( 'VIEW UPLOAD TOOL' ));
		
		$expHandler = new models\ExperimentHandler( );
		$expCount = $expHandler->fetchExperimentCount( );
		$buttons = $expHandler->fetchExperimentToolbar( );
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"TABLE_TITLE" => "Experiment List",
			"ROW_COUNT" => $expCount,
			"WEB_NAME_ABBR" => CONFIG['WEB']['WEB_NAME_ABBR'],
			"UPLOAD_VALID" => $isMember,
			"SHOW_TOOLBAR" => true,
			"BUTTONS" => $buttons
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Experiment' />" );
		$this->headerParams->set( "TITLE", "Experiment Listing | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "experiment" . DS . "ExperimentListing.tpl", $params, false );
				
	}
	
	/**
	 * View
	 * A summary view showing various details about 1 particular
	 * experiment, including the list of files it represents
	 */
	
	public function View( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW EXPERIMENTS' ));
	
		// If we're not passed an ID, show 404
		if( !isset( $_GET['id'] ) || !is_numeric( $_GET['id'] )) {
			lib\Session::sendPageNotFound( );
		}
		
		$expHandler = new models\ExperimentHandler( );
		$expInfo = $expHandler->fetchExperiment( $_GET['id'] );
		
		// If we got an id but it's invalid
		// show 404 error
		if( !$expInfo ) {
			lib\Session::sendPageNotFound( );
		}
		
		// Get a set of experiment fields to display
		$expDetails = $expHandler->fetchFormattedExperimentDetails( $expInfo );
		
		$showFiles = false;
		if( lib\Session::validateCredentials( lib\Session::getPermission( 'VIEW FILES' )) ) {
			// Fetch requirements for building the file listing
			$fileHandler = new models\FileHandler( );
			$fileCount = $fileHandler->fetchFileCount( array( $_GET['id'] ), true );
			$showFiles = true;
			
			// Add the files JS so we can display the files table
			$addonJS = $this->footerParams->get( 'ADDON_JS' );
			$addonJS[] = "orca-files.js";
			
			$this->footerParams->set( 'ADDON_JS', $addonJS );
		}
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"TITLE" => "Experiment Details",
			"SUBHEAD" => "The following is a detail summary for the experiment (<span class='text-success'>#" . $expInfo->experiment_id . "</span>): <strong><span class='text-success'>" . $expInfo->experiment_name . "</span></strong>",
			"EXPERIMENT_NAME" => $expInfo->experiment_name,
			"EXPERIMENT_ID" => $expInfo->experiment_id,
			"DETAILS" => $expDetails,
			"ROW_COUNT" => $fileCount,
			"WEB_NAME_ABBR" => CONFIG['WEB']['WEB_NAME_ABBR'],
			"SHOW_TOOLBAR" => false,
			"IDS" => $_GET['id'],
			"INCLUDE_BG" => "true",
			"TYPE" => "exp",
			"TABLE_TITLE" => "Raw File List",
			"BUTTONS" => array( ),
			"SHOW_FILES" => $showFiles
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Experiment' />" );
		$this->headerParams->set( "TITLE", "Experiment: " . $expInfo->experiment_name . " | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "experiment" . DS . "ExperimentView.tpl", $params, false );
				
	}

}

?>