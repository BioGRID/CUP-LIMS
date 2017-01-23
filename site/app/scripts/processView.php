<?PHP

/**
 * Execute a process used in the handling of views
 * and continue processing until completion
 */

ignore_user_abort(true);
set_time_limit(0);

header( 'Content-Encoding: none' );
header( 'Content-Length: ' . ob_get_length( ) );
header( 'Connection: close' );

ob_start( );
echo json_encode( array( "MESSAGE" => "Started Processing of View 1", "STATUS" => "SUCCESS" ));
ob_end_flush( );
ob_flush( );
flush( );

// require_once __DIR__ . '/../../app/lib/Bootstrap.php';

// use ORCA\app\classes\models;

sleep( 30 );

// $postData = json_decode( $_POST['expData'], true );

// $viewHandler = new models\ViewHandler( );
// $viewHandler->updateViewState( $postData['viewID'], "complete" );

// exit( );
 
?>