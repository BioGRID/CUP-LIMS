<?php


namespace ORCA\app\classes\controllers;

/**
 * Home Controller
 * This controller handles the processing of the main homepage.
 */
 
use ORCA\app\lib;

class HomeController extends lib\Controller {
	
	public function __construct( $twig ) {
		parent::__construct( $twig );
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "' />" );
		$this->headerParams->set( "TITLE", CONFIG['WEB']['WEB_NAME'] . " | " . CONFIG['WEB']['WEB_DESC'] );
	}
	
	/**
	 * Index
	 * Default layout for the main homepage of the site, called when no other actions
	 * are requested via the URL.
	 */
	
	public function Index( ) {
		$this->Member( );
	}
	
	/**
	 * Member
	 * Default Layout for a Logged in Member of the Site
	 */
	 
	 public function Member( ) {
		 
		lib\Session::canAccess( "observer" );
		
		$params = array( 
			"WEB_NAME" => CONFIG['WEB']['WEB_NAME'],
			"WEB_NAME_ABBR" => CONFIG['WEB']['WEB_NAME_ABBR'],
			"WEB_DESC" => CONFIG['WEB']['WEB_DESC'],
			"WEB_URL" => WEB_URL,
			"VERSION" => CONFIG['WEB']['VERSION'],
			"FIRSTNAME" => $_SESSION[SESSION_NAME]['FIRSTNAME'],
			"LASTNAME" => $_SESSION[SESSION_NAME]['LASTNAME']
		);
		
		$this->renderView( "home" . DS . "HomeIndex.tpl", $params, false );
			
	}
	
    /**
	 * Login
	 * Layout for the Login page for the site, called when a user
	 * does not have adequate permissions to view the standard news page.
	 */
	 
	 public function Login( ) { 
		 
		if( lib\Session::isLoggedIn( ) ) {
			header( 'Location: ' . WEB_URL . "/" );
		} else {
		 
			$params = array(
				"WEB_NAME_ABBR" => CONFIG['WEB']['WEB_NAME_ABBR'],
				"SHOW_ERROR" => "hidden",
				"WEB_URL" => WEB_URL,
				"IMG_URL" => IMG_URL
			);
			
			// Check to see if User is attempting to
			// Login to the site
			
			if( isset( $_POST['username'] ) ) {
				
				$user = new lib\User( );
				
				if( $user->validateByLogin( $_POST['username'], $_POST['password'], $_POST['remember'] ) ) {
					header( 'Location: ' . WEB_URL . '/' );
				} else {
					$params['SHOW_ERROR'] = '';
					$params['ERROR'] = 'Your Login Credentials are Invalid. Please try again!';
				}
				
				$params["USERNAME"] = $_POST['username'];
			}

			$this->renderView( "home" . DS . "HomeLogin.tpl", $params, false );
			
		}
	 }
	 
	/**
	 * Logout
	 * Logout of the site by invalidating the session
	 * and removing cookies.
	*/
	  
	public function Logout( ) {
		lib\Session::logout( );
		header( 'Location: ' . WEB_URL . '/' );
	}	

}

?>