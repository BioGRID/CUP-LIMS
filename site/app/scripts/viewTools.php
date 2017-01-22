<?PHP

/**
 * Execute a process used in the handling of views
 * and process the results
 */

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
			
	}
}

exit( );
 
?>