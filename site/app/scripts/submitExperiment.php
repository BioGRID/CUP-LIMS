<?php

/**
 * Handle the submission of an experiment
 * and deal with all the associated files
 * and database insert calls
 */
 
// header('HTTP/1.1 500 Internal Server Error');
// header('Content-type: text/plain');
// die( );

session_start( );

require_once __DIR__ . '/../../app/lib/Bootstrap.php';

use ORCA\app\classes\models;
$experiments = new models\Experiments( );

$response = array( );

if( isset( $_POST['expData'] )) {
	$expData = json_decode( $_POST['expData'] );
	
	if( isset( $expData->experimentName ) && 
		isset( $expData->experimentDate ) && 
		isset( $expData->experimentDesc ) && 
		isset( $expData->experimentCode ) &&
		isset( $expData->experimentCell ) &&
		isset( $expData->experimentHasFile ) && 
		isset( $expData->experimentFiles )) {
	
		$response = $experiments->insertExperiment( $expData );
		
	} else {
		$response = array( "STATUS" => "error", "MESSAGE" => "Unable to Process Experiment due to Missing Fields in Submission" );
	}
	
} else {
	$response = array( "STATUS" => "error", "MESSAGE" => "No Data Sent for Processing" );
}

echo json_encode( $response );
 
?>