<?php


namespace ORCA\app\classes\controllers;

/**
 * Upload Controller
 * This controller handles the processing of the dataset upload functionality.
 */
 
use ORCA\app\lib;
use ORCA\app\classes\models;

class UploadController extends lib\Controller {
	
	public function __construct( $twig ) {
		parent::__construct( $twig );
		
		$addonJS = array( );
		$addonJS[] = "dropzone.min.js";
		$addonJS[] = "bootstrap-datepicker.min.js";
		$addonJS[] = "formValidation/formValidation.min.js";
		$addonJS[] = "formValidation/bootstrap.min.js";
		
		
		$addonCSS = array( );
		$addonCSS[] = "dropzone.min.css";
		$addonCSS[] = "bootstrap-datepicker3.min.css";
		$addonCSS[] = "formValidation/formValidation.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
	}
	
	/**
	 * Index
	 * Default layout for the main upload page, called when no other actions
	 * are requested via the URL.
	 */
	
	public function Index( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW UPLOAD TOOL' ));
		
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "upload/orca-upload.js";
		
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$fileHandler = new models\FileHandler( );
		$annotationFiles = $fileHandler->fetchAnnotationFiles( );
		
		$groupHandler = new models\GroupHandler( );
		$groups = $groupHandler->fetchGroups( );
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"ANNOTATION_FILES" => $annotationFiles,
			"DATASET_CODE" => uniqid( ),
			"GROUPS" => $groups,
			"TODAY" => date( 'Y-m-d', strtotime( 'today' ))
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Upload' />" );
		$this->headerParams->set( "TITLE", "Upload Experiment" );
		
		$this->renderView( "upload" . DS . "UploadIndex.tpl", $params, false );
				
	}
	
	/**
	 * Annotation
	 * Default layout for the annotation upload page, used to upload
	 * sets of sgRNA and their mapping to groups
	 */
	
	public function Annotation( ) {
		
		lib\Session::canAccess( lib\Session::getPermission( 'VIEW UPLOAD ANNOTATION TOOL' ));
		
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "upload/orca-upload-annotation.js";
		
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$lookups = new models\Lookups( );
		$organisms = $lookups->buildOrganismHash( );
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"DATASET_CODE" => uniqid( ),
			"ORGANISMS" => $organisms,
			"TODAY" => date( 'Y-m-d', strtotime( 'today' ))
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Upload/Annotation' />" );
		$this->headerParams->set( "TITLE", "Upload Annotation" );
		
		$this->renderView( "upload" . DS . "UploadAnnotation.tpl", $params, false );
				
	}

}

?>