<?php

namespace ORCA\app\lib;

/**
 * NAVBAR
 * This class contains a static array that can be customized to modify
 * the site Navbar.
 *
 * - Status can be "public", "observer", "curator", "poweruser", "admin"
 * - public - Seen by all, even non-logged in users
 * - observer - Lowest Class, sees Observer and Public links
 * - curator - General Class, sees Curator, Observer, and Public Links
 * - poweruser - Slightly Elevated User, sees PowerUser, Curator, Observer, and Public Links
 * - admin - Highest User Class, sees All Links
 */
 
use ORCA\app\lib;
 
class Navbar {

	public static $leftNav;
	public static $rightNav;
	
	public static function init( ) {
			
		self::$leftNav = array( );
		self::$rightNav = array( );
		
		// LEFT SIDE OF NAVBAR
		self::$leftNav['Home'] = array( "URL" => WEB_URL, "TITLE" => 'Return to Homepage', "STATUS" => 'public' );
		self::$leftNav['Files'] = array( "URL" => WEB_URL . "/Files", "TITLE" => 'View Files', "STATUS" => 'observer' );
		self::$leftNav['Upload'] = array( "URL" => "#", "TITLE" => 'Upload Dataset', "STATUS" => lib\Session::getPermission( 'VIEW UPLOAD TOOL' ), "DROPDOWN" => array( ));
		self::$leftNav['Upload']['DROPDOWN']['Upload Files'] = array( "URL" => WEB_URL . "/Upload", "TITLE" => 'Upload New Files', "STATUS" => lib\Session::getPermission( 'VIEW UPLOAD TOOL' ));
		self::$leftNav['Upload']['DROPDOWN']['Upload Annotation'] = array( "URL" => WEB_URL . "/Upload/Annotation", "TITLE" => 'Upload New Annotation', "STATUS" => lib\Session::getPermission( 'VIEW UPLOAD ANNOTATION TOOL' ) );
		self::$leftNav['Views'] = array( "URL" => WEB_URL . "/View", "TITLE" => 'View Datasets', "STATUS" => lib\Session::getPermission( 'VIEW VIEWS' ));
		self::$leftNav['Wiki'] = array( "URL" => CONFIG['WEB']['WIKI_URL'], "TITLE" => 'ORCA Help Wiki', "STATUS" => 'observer' );
		
		// RIGHT SIDE OF NAVBAR
		self::$rightNav['Admin'] = array( "URL" => "#", "TITLE" => 'Administration Utilities', "STATUS" => 'observer', "DROPDOWN" => array( ) );
		self::$rightNav['Admin']['DROPDOWN']['Add User'] = array( "URL" => WEB_URL . "/Admin/AddUser", "TITLE" => 'Add New User', "STATUS" => 'poweruser'  );
		self::$rightNav['Admin']['DROPDOWN']['Change Password'] = array( "URL" => WEB_URL . "/Admin/ChangePassword", "TITLE" => 'Change Password', "STATUS" => 'observer'  );
		self::$rightNav['Admin']['DROPDOWN']['Manage Users'] = array( "URL" => WEB_URL . "/Admin/ManageUsers", "TITLE" => 'Manage Users and Status', "STATUS" => 'poweruser' );
		self::$rightNav['Admin']['DROPDOWN']['Manage Permissions'] = array( "URL" => WEB_URL . "/Admin/ManagePermissions", "TITLE" => 'Manage Page Access Permissions', "STATUS" => 'admin' );
		self::$rightNav['Admin']['DROPDOWN']['Manage Groups'] = array( "URL" => WEB_URL . "/Admin/ManageGroups", "TITLE" => 'Manage Groups', "STATUS" => 'poweruser' );
		self::$rightNav['Logout'] = array( "URL" => WEB_URL . "/Home/Logout", "TITLE" => 'Logout from Site', "STATUS" => 'observer' );
	
	}
	
}