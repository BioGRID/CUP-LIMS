<?php

/**
 * Handle the submission of an experiment
 * and deal with all the associated files
 * and database insert calls
 */
 
use ORCA\app\lib;
 
// header('HTTP/1.1 500 Internal Server Error');
// header('Content-type: text/plain');
// die( );

session_start( );

require_once __DIR__ . '/../../app/lib/Bootstrap.php';

if( !lib\Session::isLoggedIn( ) ) {
	die( json_encode( array( "STATUS" => "error", "MESSAGE" => "Your Session has Expired, please logout and log back in again!" )));
}

use ORCA\app\classes\models;
$fileHandler = new models\FileHandler( );

$response = array( );

if( isset( $_POST['data'] )) {
	$data = json_decode( $_POST['data'] );
	
	if( isset( $data->fileDesc ) && 
		isset( $data->filePermission ) &&
		isset( $data->fileGroups ) &&
		isset( $data->fileDate ) && 
		isset( $data->fileCode ) &&
		isset( $data->fileTags ) &&
		isset( $data->fileAnnotation ) &&
		isset( $data->hasFile ) && 
		isset( $data->files )) {
	
		$response = $fileHandler->insertFileSet( $data );
		
	} else {
		$response = array( "STATUS" => "error", "MESSAGE" => "Unable to Process Files due to Missing Fields in Submission" );
	}
	
} else {
	$response = array( "STATUS" => "error", "MESSAGE" => "No Data Sent for Processing" );
}

exit( json_encode( $response ) );
 
?>