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
			
	}
}

exit( );
 
?>