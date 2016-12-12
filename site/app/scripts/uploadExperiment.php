<?php

/**
 * Handle the movement and processing of uploaded files
 * into the correct upload location
 */
 
// header('HTTP/1.1 500 Internal Server Error');
// header('Content-type: text/plain');
// die( );

require_once __DIR__ . '/../../app/lib/Bootstrap.php';

$status = "error";
$file = "";

if( !empty( $_FILES )) {
	
	// Get Uploaded File from Dropzone
	$tempFile = $_FILES['file']['tmp_name'];
	
	// Generate random directory for it to prevent possible
	// conflicts
	$directory = UPLOAD_TMP_PATH . DS . $_POST['experimentCode'];
	
	if( !is_dir( $directory )) {
		mkdir( $directory, 0777, false );
	}
	
	// Remove any existing files, in case mistake uploads
	// occurred previously
	// array_map( 'unlink', glob( $directory . DS . "*.*" ));
	
	// Move the file to the new directory which is just a temporary
	// home until we have a chance to move it to a permanent one
	$targetFile = $directory . DS . $_FILES['file']['name'];
	if( move_uploaded_file( $tempFile, $targetFile )) {
		$status = "success";
		$filename = $targetFile;
	}
} 

exit( );
 
?>