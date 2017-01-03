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
		$addonJS[] = "admin/orca-admin-changePassword.js";
		
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
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Admin/ChangePassword' />" );
		$this->headerParams->set( "TITLE", "Change Password | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "admin" . DS . "AdminChangePassword.tpl", $params, false );
				
	}
	
	/**
	 * Manage Users
	 * A tool for changing permissions and status levels of different users
	 * of the system.
	 */
	
	public function ManageUsers( ) {
		
		lib\Session::canAccess( "poweruser" );
		
		// Add some Manage Users Specific JS
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "jquery.qtip.min.js";
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "alertify.min.js";
		$addonJS[] = "admin/orca-admin-manageUsers.js";
		
		// Add some Manage Users Specific CSS
		$addonCSS = $this->headerParams->get( 'ADDON_CSS' );
		$addonCSS[] = "jquery.qtip.min.css";
		$addonCSS[] = "dataTables.bootstrap.css";
		$addonCSS[] = "alertify.min.css";
		$addonCSS[] = "alertify-bootstrap.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$userHandler = new models\UserHandler( );
		$userCount = $userHandler->fetchUserCount( );
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"USER_COUNT" => $userCount
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Admin/ManagerUsers' />" );
		$this->headerParams->set( "TITLE", "Manage Users | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "admin" . DS . "AdminManageUsers.tpl", $params, false );
				
	}
	
	/**
	 * Add User
	 * A tool for adding a new user to the system that can
	 * then login to the site successfully
	 */
	
	public function AddUser( ) {
		
		lib\Session::canAccess( "poweruser" );
		
		// Add some Change Password Specific JS
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "formValidation/formValidation.min.js";
		$addonJS[] = "formValidation/bootstrap.min.js";
		$addonJS[] = "admin/orca-admin-addUser.js";
		
		// Add some Change Password Specific CSS
		$addonCSS = $this->headerParams->get( 'ADDON_CSS' );
		$addonCSS[] = "formValidation/formValidation.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
				
		$userHandler = new models\UserHandler( );
		$userClasses = $userHandler->fetchUserClasses( );
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"USER_CLASSES" => $userClasses
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Admin/AddUser' />" );
		$this->headerParams->set( "TITLE", "Add User | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "admin" . DS . "AdminAddUser.tpl", $params, false );
				
	}
	
	/**
	 * Manage Permissions
	 * A tool for adding and managing permission values and the settings 
	 * each one is configured to
	 */

	 public function ManagePermissions( ) {
		
		lib\Session::canAccess( "admin" );
		
		// Add some Manage Permissions Specific JS
		$addonJS = $this->footerParams->get( 'ADDON_JS' );
		$addonJS[] = "jquery.qtip.min.js";
		$addonJS[] = "jquery.dataTables.js";
		$addonJS[] = "dataTables.bootstrap.js";
		$addonJS[] = "alertify.min.js";
		$addonJS[] = "admin/orca-admin-managePermissions.js";
		
		// Add some Manager Permissions Specific CSS
		$addonCSS = $this->headerParams->get( 'ADDON_CSS' );
		$addonCSS[] = "jquery.qtip.min.css";
		$addonCSS[] = "dataTables.bootstrap.css";
		$addonCSS[] = "alertify.min.css";
		$addonCSS[] = "alertify-bootstrap.min.css";
		
		$this->headerParams->set( 'ADDON_CSS', $addonCSS );
		$this->footerParams->set( 'ADDON_JS', $addonJS );
		
		$permHandler = new models\PermissionsHandler( );
		$permCount = $permHandler->fetchPermissionCount( );
				
		$params = array(
			"WEB_URL" => WEB_URL,
			"IMG_URL" => IMG_URL,
			"PERMISSION_COUNT" => $permCount
		);
		
		$this->headerParams->set( "CANONICAL", "<link rel='canonical' href='" . WEB_URL . "/Admin/ManagePermissions' />" );
		$this->headerParams->set( "TITLE", "Manage Permissions | " . CONFIG['WEB']['WEB_NAME'] );
		
		$this->renderView( "admin" . DS . "AdminManagePermissions.tpl", $params, false );
				
	}
	 
}

?>