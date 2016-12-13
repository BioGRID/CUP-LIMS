<?PHP

/**
 * Execute a process used in the handling of data files
 * and process the results
 */

session_start( );

require_once __DIR__ . '/../../app/lib/Bootstrap.php';

use ORCA\app\classes\models\RawDataHandler;
use ORCA\app\classes\models\FileHandler;

if( isset( $_POST['script'] ) ) {
	
	$full = false;
	if( isset( $_POST['performFull']) && strtolower($_POST['performFull']) == 'true' ) {
		$full = true;
	}	
	
	switch( $_POST['script'] ) {
		
		case 'fetchFiles' :
			$fileHandler = new FileHandler( );
			$files = $fileHandler->fetchFiles( $_POST['expID'], $full );
			echo json_encode( $files );
			break;
			
		case 'parseFile' :
			$rawData = new RawDataHandler( );
			echo json_encode( array( "STATUS" => "ERROR", "MESSAGE" => "PROBLEM!!!" ));
			break;
			
	}
}

exit( );
 
?>