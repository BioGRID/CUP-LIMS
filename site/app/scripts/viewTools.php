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
			if( lib\Session::validateCredentials( lib\Session::getPermission( 'CREATE VIEW' )) && isset( $postData['viewName'] ) && isset( $postData['viewDesc'] ) && isset( $postData['viewType'] ) && isset( $postData['viewValue'] ) && isset( $postData['viewFiles'] )) {
				$viewHandler = new models\ViewHandler( );
				
				if( $data = $viewHandler->addView( $postData['viewName'], $postData['viewDesc'], $postData['viewType'], $postData['viewValue'], $postData['viewFiles'] )) {
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
			
	}
}

exit( );
 
?>