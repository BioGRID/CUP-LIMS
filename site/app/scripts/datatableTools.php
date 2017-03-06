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
			
		// Fetch the column header for the experiment view
		// Datatable with correct options
		case 'experimentHeader' :
			$expHandler = new models\ExperimentHandler( );
			$expHeader = $expHandler->fetchExperimentColumnDefinitions( );
			echo json_encode( $expHeader );
			break;
		
		// Fetch rows for the experiment view
		// tool for display in Datatables
		case 'experimentRows' :
			$draw = $postData['draw'];
			
			$expHandler = new models\ExperimentHandler( );
			$expRows = $expHandler->buildExperimentRows( $postData );
			$recordsFiltered = $expHandler->getUnfilteredExperimentCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $expRows ));
			break;
			
		// Fetch column header for the files view
		// Datatable with correct options
		case 'filesHeader' :
			$fileHandler = new models\FileHandler( );
			$showBGSelect = false;
			if( isset( $postData['showBGSelect'] ) && $postData['showBGSelect'] == "true" ) {
				$showBGSelect = true;
			}
			$fileHeader = $fileHandler->fetchFilesViewColumnDefinitions( $showBGSelect );
			echo json_encode( $fileHeader );
			break;
			
		// Fetch rows for the files view
		// tool for display in Datatables
		case 'filesRows' :
			$draw = $postData['draw'];
			
			$fileHandler = new models\FileHandler( );
			$fileRows = $fileHandler->buildFileRows( $postData );
			$recordsFiltered = $fileHandler->getUnfilteredFileCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $fileRows ));
			break;
			
		// Fetch the column header for the view listing
		// Datatable with correct options
		case 'viewHeader' :
			$viewHandler = new models\ViewHandler( );
			$viewHeader = $viewHandler->fetchViewColumnDefinitions( );
			echo json_encode( $viewHeader );
			break;
		
		// Fetch rows for the view listing
		// tool for display in Datatables
		case 'viewRows' :
			$draw = $postData['draw'];
			
			$viewHandler = new models\ViewHandler( );
			$viewRows = $viewHandler->buildViewRows( $postData );
			$recordsFiltered = $viewHandler->getUnfilteredViewCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $viewRows ));
			break;
			
		// Fetch the column header for the matrix view listing
		// Datatable with correct options
		case 'matrixViewHeader' :
			$matrixHandler = new models\MatrixViewHandler( $postData['viewID'] );
			$matrixHeader = $matrixHandler->fetchColumnDefinitions( $postData ); 
			echo json_encode( $matrixHeader );
			break;
		
		// Fetch rows for the matrix view listing
		// tool for display in Datatables
		case 'matrixViewRows' :
			$draw = $postData['draw'];
			
			$matrixHandler = new models\MatrixViewHandler( $postData['viewID'] );
			$matrixRows = $matrixHandler->buildRows( $postData );
			$recordsFiltered = $matrixHandler->getUnfilteredCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $matrixRows ));
			break;
			
		// Fetch the column header for the raw reads
		// Datatable with correct options
		case 'rawReadsHeader' :
			$rawReadsHandler = new models\RawAnnotatedViewHandler( $postData['viewID'] );
			$rawHeader = $rawReadsHandler->fetchColumnDefinitions( );
			echo json_encode( $rawHeader );
			break;
		
		// Fetch rows for the raw reads
		// tool for display in Datatables
		case 'rawReadsRows' :
			$draw = $postData['draw'];
			
			$rawReadsHandler = new models\RawAnnotatedViewHandler( $postData['viewID'] );
			$rawRows = $rawReadsHandler->buildRows( $postData );
			$recordsFiltered = $rawReadsHandler->getUnfilteredRowCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $rawRows ));
			break;
			
		// Fetch the column header for the Manage Groups
		// Datatable with correct options
		case 'manageGroupHeader' :
			$groupHandler = new models\GroupHandler( );
			$groupHeader = $groupHandler->fetchManageGroupColumnDefinitions( );
			echo json_encode( $groupHeader );
			break;
		
		// Fetch user rows for the Manage Groups
		// tool for display in Datatables
		case 'manageGroupRows' :
			$draw = $postData['draw'];
			
			$groupHandler = new models\GroupHandler( );
			$groupRows = $groupHandler->buildManageGroupRows( $postData );
			$recordsFiltered = $groupHandler->getUnfilteredGroupCount( $postData );
			
			echo json_encode( array( "draw" => $draw, "recordsTotal" => $postData['totalRecords'], "recordsFiltered" => $recordsFiltered, "data" => $groupRows ));
			break;
			
	}
}

exit( );
 
?>