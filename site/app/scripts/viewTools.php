<?PHP

/**
 * Execute a process used in the handling of views
 * and process the results
 */

session_start( );
 
require_once __DIR__ . '/../../app/lib/Bootstrap.php';

use ORCA\app\lib;
use ORCA\app\classes\models;

$postData = json_decode( $_POST['expData'], true );

if( isset( $postData['tool'] ) ) {	
	
	switch( $postData['tool'] ) {
		
		// Check to see if a View has completed building
		case 'checkViewBuildProgress' :
			$viewHandler = new models\ViewHandler( );
			$viewState = $viewHandler->fetchViewState( $postData['viewID'] );
			echo json_encode( array( "STATE" => $viewState ) );
			break;
			
		// Add a new view to the view table
		case 'addView' :
			$results = array( );
			if( lib\Session::validateCredentials( lib\Session::getPermission( 'CREATE VIEW' )) && isset( $postData['viewName'] ) && isset( $postData['viewDesc'] ) && isset( $postData['viewType'] ) && isset( $postData['viewValue'] ) && isset( $postData['viewFiles'] ) && isset( $postData['viewPermission'] ) && isset( $postData['viewGroups'] )) {
				$viewHandler = new models\ViewHandler( );
				
				if( $data = $viewHandler->addView( $postData['viewName'], $postData['viewDesc'], $postData['viewType'], $postData['viewValue'], $postData['viewFiles'], $postData['viewPermission'], $postData['viewGroups'] )) {
					$results = array( "STATUS" => "SUCCESS", "MESSAGE" => "Successfully Added New View", "ID" => $data['ID'] );
				} else {
					$results = array( "STATUS" => "ERROR", "MESSAGE" => "The View Name you Entered Already Exists" );
				}
				
			} else {
				$results = array( "STATUS" => "ERROR", "MESSAGE" => "Unable to add a new view at this time, please try again later!" );
			}
			
			echo json_encode( $results );
			break;
			
		// Disable a view in the database
		case 'disableView' :
			
			$results = array( );
			if( lib\Session::validateCredentials( lib\Session::getPermission( 'MANAGE VIEWS' )) && isset( $postData['views'] )) {
			
				if( sizeof( $postData['views'] ) > 0 ) {
					$viewHandler = new models\ViewHandler( );
					$viewHandler->disableViews( $postData['views'] );
					$results = array( "STATUS" => "SUCCESS", "MESSAGE" => "Views successfully disabled" );
				} else {
					$results = array( "STATUS" => "ERROR", "MESSAGE" => "No view ids were specified. Nothing done..." );
				}
				
			} else {
				$results = array( "STATUS" => "ERROR", "MESSAGE" => "You do not have Valid Permission to Perform this Action" );
			}
				
			echo json_encode( $results );
			break;
		
		// Fetch formatted annotation to display in a popup
		// for a given view group
		case 'fetchGroupAnnotation' :
		
			$results = array( "DATA" => "" );
			if( isset( $postData['viewID'] ) && isset( $postData['id'] )) {
				$viewHandler = new models\ViewHandler( );
				$results["DATA"] = $viewHandler->fetchFormattedGroupAnnotation( $postData['viewID'], $postData['id'] );
			}
			
			echo json_encode( $results );
			break;
			
		// Fetch formatted annotation to display in a popup
		// for a given view group
		case 'fetchMatrixHeaderPopup' :
		
			$results = array( "DATA" => "" );
			if( isset( $postData['viewID'] ) && isset( $postData['fileID'] ) && isset( $postData['fileName'] ) && isset( $postData['bgID'] ) && isset( $postData['bgName'] )) {
				$matrixHandler = new models\MatrixViewHandler( $postData['viewID'] );
				$results["DATA"] = $matrixHandler->fetchFormattedHeaderAnnotation( $postData['fileID'], $postData['fileName'], $postData['bgID'], $postData['bgName'] );
			}
			
			echo json_encode( $results );
			break;
			
		// Fetch formatted Raw Read information to display in the matrix view
		case 'fetchMatrixRawReads' :
			$results = array( "DATA" => "" );
			if( isset( $postData['fileID'] ) && isset( $postData['groupID'] ) && isset( $postData['fileName'] ) && isset( $postData['groupName'] )) {
				$viewHandler = new models\ViewHandler( );
				$results["DATA"] = $viewHandler->fetchRawReadsSummaryByGroupID( $postData['fileID'], $postData['fileName'], $postData['groupID'], $postData['groupName'], $postData['scoreVal'] );
			}
			
			echo json_encode( $results );
			break;
			
		// Fetch a nice layout of privacy details
		// to show in a popup
		case 'fetchViewPrivacyDetails' :
			$viewHandler = new models\ViewHandler( );
			$results = $viewHandler->fetchFormattedViewPrivacyDetails( $postData['viewID'] );
			echo json_encode( array( "DATA" => $results ));
			break;
			
		// Perform a change permission operation
		// for a single view
		case 'changePermission' :
			
			$results = array( );
			if( lib\Session::isLoggedIn( ) && isset( $postData['viewID'] ) && isset( $postData['viewPermission'] ) && isset( $postData['viewGroups'] )) {
				
				$viewHandler = new models\ViewHandler( );
				if( $viewHandler->changePermission( $postData['viewID'], $postData['viewPermission'], $postData['viewGroups'] ) ) {
					$results = array( "STATUS" => "success", "MESSAGE" => "View Permissions Successfully Changed!" );
				} else {
					$results = array( "STATUS" => "error", "MESSAGE" => "Unable to Change View Permissions. Please Try Again Later." );
				}
				
			} else {
				$results = array( "STATUS" => "error", "MESSAGE" => "Unable to Change View Permissions. Please Try Again Later." );
			}
			
			echo json_encode( $results );
			break;
			
	}
}

exit( );
 
?>