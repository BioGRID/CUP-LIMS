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
		
		case 'changePassword' :
			
			$results = array( );
			if( lib\Session::isLoggedIn( ) && isset( $postData['newPassword'] ) && isset( $postData['currentPassword'] )) {
				
				$userID = $_SESSION[SESSION_NAME]['ID'];
				$userName = $_SESSION[SESSION_NAME]['NAME'];
				
				$user = new lib\User( );
				if( $user->verifyPassword( $userName, $postData['currentPassword'] )) {
					$user->changePassword( $userID, $postData['newPassword'] );
					$results = array( "STATUS" => "success", "MESSAGE" => "Password Successfully Changed!" );
					lib\Session::logout( );
				} else {
					$results = array( "STATUS" => "error", "MESSAGE" => "The password you entered for 'current password' does not match your current password..." );
				}
				
			} else if( lib\Session::validateCredentials( 'admin' ) && isset( $postData['newPassword'] ) && isset( $postData['userID'] )) {
				
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
			
		case 'manageUsersHeader' :
			$userHandler = new models\UserHandler( );
			$usersHeader = $userHandler->fetchManageUsersColumnDefinitions( );
			echo json_encode( $usersHeader );
			break;
			
		case 'manageUsersRows' :
		
			$draw = $postData['draw'];
		
			$userHandler = new models\UserHandler( );
			$userRows = $userHandler->buildManageUserRows( $postData );
			
			$recordsFiltered = $userHandler->getUnfilteredUsersCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $userRows ));
			break;
			
	}
}

exit( );
 
?>