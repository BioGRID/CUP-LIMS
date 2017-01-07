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
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "orca-dataTableBlock.js";
		$addonJS[] = "orca-experiment.js";
		
		$addonCSS = array( );
		$addonCSS[] = "dataTables.bootstrap.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
	}
	
	/**
	 * Index
	 * Default layout for the main experiment page, called when no other actions
	 * are requested via the URL.
	 */
	 
	public function Index( ) {
		$this->View( );
	}
	
	/**
	 * View
	 * Main view for the experiment page. Presents a table of uploaded experiments
	 * with the ability to search, sort, browse results.
	 */
	
	public function View( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW EXPERIMENTS' ));
		
		$isMember = lib\Session::validateCredentials( lib\Session::getPermission( 'VIEW UPLOAD TOOL' ));
		
		$expHandler = new models\ExperimentHandler( );
		$expCount = $expHandler->fetchExperimentCount( );
		$buttons = $expHandler->fetchExperimentToolbar( );
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"TABLE_TITLE" => "Loaded Experiments",
			"ROW_COUNT" => $expCount,
			"WEB_NAME_ABBR" => CONFIG['WEB']['WEB_NAME_ABBR'],
			"UPLOAD_VALID" => $isMember,
			"SHOW_TOOLBAR" => true,
			"BUTTONS" => $buttons
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Experiment' />" );
		$this->headerParams->set( "TITLE", "View Experiments | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "experiment" . DS . "ExperimentIndex.tpl", $params, false );
				
	}

}

?>