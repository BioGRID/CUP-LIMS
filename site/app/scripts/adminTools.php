<?PHP

/**
 * Execute a process used in the handling of data files
 * and process the results
 */

session_start( );

require_once __DIR__ . '/../../app/lib/Bootstrap.php';

use ORCA\app\lib;
use ORCA\app\classes\models;

$postData = json_decode( $_POST['expData'], true );

if( isset( $postData['adminTool'] ) ) {	
	
	switch( $postData['adminTool'] ) {
		
		// Perform a change password operation
		// for a single user 
		case 'changePassword' :
			
			$results = array( );
			if( lib\Session::isLoggedIn( ) && isset( $postData['newPassword'] ) && isset( $postData['currentPassword'] )) {
				
				$userID = $_SESSION[SESSION_NAME]['ID'];
				$userName = $_SESSION[SESSION_NAME]['NAME'];
				
				// If changing for the user making the request
				// verify their existing password first, before
				// performing change
				$user = new lib\User( );
				if( $user->verifyPassword( $userName, $postData['currentPassword'] )) {
					$user->changePassword( $userID, $postData['newPassword'] );
					$results = array( "STATUS" => "success", "MESSAGE" => "Password Successfully Changed!" );
					lib\Session::logout( );
				} else {
					$results = array( "STATUS" => "error", "MESSAGE" => "The password you entered for 'current password' does not match your current password..." );
				}
				
			} else if( lib\Session::validateCredentials( 'admin' ) && isset( $postData['newPassword'] ) && isset( $postData['userID'] )) {
				
				// If permission level is high enough, this tool allows
				// for changing of anyones password. Does not require
				// original password verification
				$user = new lib\User( );
				$user->changePassword( $postData['userID'], $postData['newPassword'] );
				$results = array( "STATUS" => "success", "MESSAGE" => "Password Successfully Changed!" );
				
				if( $postData['userID'] == $_SESSION[SESSION_NAME]['ID'] ) {
					lib\Session::logout( );
				}
				
			} else {
				$results = array( "STATUS" => "error", "MESSAGE" => "Unable to change password at this time, please try again later!" );
			}
			
			echo json_encode( $results );
			break;
		
		// Fetch the column header for the Manage Users
		// Datatable with correct options
		case 'manageUsersHeader' :
			$userHandler = new models\UserHandler( );
			$usersHeader = $userHandler->fetchManageUsersColumnDefinitions( );
			echo json_encode( $usersHeader );
			break;
		
		// Fetch user rows for the Manage Users
		// tool for display in Datatables
		case 'manageUsersRows' :
			$draw = $postData['draw'];
			
			$userHandler = new models\UserHandler( );
			$userRows = $userHandler->buildManageUserRows( $postData );
			$recordsFiltered = $userHandler->getUnfilteredUsersCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $userRows ));
			break;
		
		// Change the User Class Up or Down
		// for promoting/demoting users
		case 'userClassChange' :
		
			$results = array( );
			if( lib\Session::validateCredentials( 'poweruser' ) && isset( $postData['userID'] ) && isset( $postData['direction'] )) {
				$user = new lib\User( );
				$newClass = $user->changeUserLevel( $postData['userID'], $postData['direction'] );
				if( $newClass ) {
					$results = array( "STATUS" => "SUCCESS", "MESSAGE" => "Successfully Changed User Class", "NEWVAL" => $newClass );
				} else {
					$results = array( "STATUS" => "ERROR", "MESSAGE" => "You do not have High Enough Permissions to Perform this Action" );
				}
			} else {
				$results = array( "STATUS" => "ERROR", "MESSAGE" => "You do not have Valid Permission to Perform this Action" );
			}
			
			echo json_encode( $results );
			break;
			
		// Change the User Status between active
		// and inactive based on the current setting
		case 'userStatusChange' :
		
			$results = array( );
			if( lib\Session::validateCredentials( 'poweruser' ) && isset( $postData['userID'] ) && isset( $postData['status'] )) {
				$user = new lib\User( );
				$newStatus = $user->changeUserStatus( $postData['userID'], $postData['status'] );
				if( $newStatus ) {
					$results = array( "STATUS" => "SUCCESS", "MESSAGE" => "Successfully Changed User Status", "NEWVAL" => $newStatus );
				} else {
					$results = array( "STATUS" => "ERROR", "MESSAGE" => "You do not have High Enough Permissions to Perform this Action" );
				}
			} else {
				$results = array( "STATUS" => "ERROR", "MESSAGE" => "You do not have Valid Permission to Perform this Action" );
			}
			
			echo json_encode( $results );
			break;
			
		// Change the User Status between active
		// and inactive based on the current setting
		case 'addNewUser' :
		
			$results = array( );
			if( lib\Session::validateCredentials( 'poweruser' ) && isset( $postData['userName'] ) && isset( $postData['userPassword'] ) && isset( $postData['userFirstName'] ) && isset( $postData['userLastName'] ) && isset( $postData['userEmail'] ) && isset( $postData['userClass'] )) {
				$user = new lib\User( );
				
				if( !$user->usernameExists( $postData['userName'] ) && !$user->emailExists( $postData['userEmail'] )) {
					$user->addUser( $postData['userName'], $postData['userPassword'], $postData['userFirstName'], $postData['userLastName'], $postData['userEmail'], $postData['userClass'] );
					$results = array( "STATUS" => "SUCCESS", "MESSAGE" => "Successfully Added New User" );
				} else {
					$results = array( "STATUS" => "ERROR", "MESSAGE" => "The Username or Email you Entered Already Belong to an Existing User" );
				}
				
			} else {
				$results = array( "STATUS" => "ERROR", "MESSAGE" => "Unable to add a new user at this time, please try again later!" );
			}
			
			echo json_encode( $results );
			break;
			
		// Fetch the column header for the Manage Permissions
		// Datatable with correct options
		case 'managePermissionsHeader' :
			$permHandler = new models\PermissionsHandler( );
			$permHeader = $permHandler->fetchManagePermissionsColumnDefinitions( );
			echo json_encode( $permHeader );
			break;
		
		// Fetch user rows for the Manage Permissions
		// tool for display in Datatables
		case 'managePermissionsRows' :
			$draw = $postData['draw'];
			
			$permHandler = new models\PermissionsHandler( );
			$permRows = $permHandler->buildManagePermissionsRows( $postData );
			$recordsFiltered = $permHandler->getUnfilteredPermissionsCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $permRows ));
			break;
			
		// Change a permission level for a given permission
		// setting option
		case 'permissionLevelChange' :
		
			$results = array( );
			if( lib\Session::validateCredentials( 'poweruser' ) && isset( $postData['permission'] ) && isset( $postData['level'] )) {
				$permHandler = new models\PermissionsHandler( );
				$newPerm = $permHandler->changePermissionLevel( $postData['permission'], $postData['level'] );
				if( $newPerm ) {
					$results = array( "STATUS" => "SUCCESS", "MESSAGE" => "Successfully Changed Permission Level" );
				} else {
					$results = array( "STATUS" => "ERROR", "MESSAGE" => "You do not have High Enough Permissions to Perform this Action" );
				}
			} else {
				$results = array( "STATUS" => "ERROR", "MESSAGE" => "You do not have Valid Permission to Perform this Action" );
			}
			
			echo json_encode( $results );
			break;
	}
}

exit( );
 
?>