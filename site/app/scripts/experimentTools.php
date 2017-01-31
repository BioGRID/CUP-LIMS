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

if( isset( $postData['tool'] ) ) {	
	
	switch( $postData['tool'] ) {
		
		// Disable an experiment in the database
		case 'disableExperiment' :
			
			$results = array( );
			if( lib\Session::validateCredentials( lib\Session::getPermission( 'MANAGE EXPERIMENTS' )) && isset( $postData['exps'] )) {
			
				if( sizeof( $postData['exps'] ) > 0 ) {
					$expHandler = new models\ExperimentHandler( );
					$expHandler->disableExperiments( $postData['exps'] );
					$results = array( "STATUS" => "SUCCESS", "MESSAGE" => "Experiments successfully disabled" );
				} else {
					$results = array( "STATUS" => "ERROR", "MESSAGE" => "No experiment ids were specified. Nothing done..." );
				}
				
			} else {
				$results = array( "STATUS" => "ERROR", "MESSAGE" => "You do not have Valid Permission to Perform this Action" );
			}
				
			echo json_encode( $results );
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
			
		// Fetch the column header for the Manage Permissions
		// Datatable with correct options
		case 'experimentHeader' :
			$expHandler = new models\ExperimentHandler( );
			$expHeader = $expHandler->fetchExperimentColumnDefinitions( );
			echo json_encode( $expHeader );
			break;
		
		// Fetch user rows for the Manage Permissions
		// tool for display in Datatables
		case 'experimentRows' :
			$draw = $postData['draw'];
			
			$expHandler = new models\ExperimentHandler( );
			$expRows = $expHandler->buildExperimentRows( $postData );
			$recordsFiltered = $expHandler->getUnfilteredExperimentCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $expRows ));
			break;
			
	}
}

exit( );
 
?>