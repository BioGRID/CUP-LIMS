<?php


namespace ORCA\app\classes\controllers;

/**
 * Admin Controller
 * This controller handles the processing of several different admin tools and options.
 */
 
use ORCA\app\lib;
use ORCA\app\classes\models;

class AdminController extends lib\Controller {
	
	public function __construct( $twig ) {
		parent::__construct( $twig );
		
		$addonJS = array( );
		
		$addonCSS = array( );
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
	}
	
	/**
	 * Index
	 * Default layout for the main admin page, called when no other actions
	 * are requested via the URL.
	 */
	
	public function Index( ) {
		
		lib\Session::canAccess( "observer" );
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Admin' />" );
		$this->headerParams->set( "TITLE", "Admin Tools | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "admin" . DS . "AdminIndex.tpl", $params, false );
				
	}
	
	/**
	 * Change Password
	 * A tool for changing your own password but also the changing of anyone's password
	 * when the user possesses the correct permissions
	 */
	
	public function ChangePassword( ) {
		
		lib\Session::canAccess( "observer" );
		
		// Add some Change Password Specific JS
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "formValidation/formValidation.min.js";
		$addonJS[] = "formValidation/bootstrap.min.js";
		$addonJS[] = "orca-admin-changePassword.js";
		
		// Add some Change Password Specific CSS
		$addonCSS = $this->headerParams->get( 'ADDON_CSS' );
		$addonCSS[] = "formValidation/formValidation.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$userList = array( );
		if( lib\Session::validateCredentials( 'admin' )) {
			$userHandler = new models\UserHandler( );
			$userList = $userHandler->buildUserList( );
		}
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"USER_LIST" => $userList
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Admin' />" );
		$this->headerParams->set( "TITLE", "Change Password | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "admin" . DS . "AdminChangePassword.tpl", $params, false );
				
	}

}

?>