<?php

/**
 * Execute a process used in the handling of data files
 * and process the results
 */

session_start( );

require_once __DIR__ . '/../../app/lib/Bootstrap.php';

use ORCA\app\lib;
use ORCA\app\classes\models;

$postData = json_decode( $_POST['data'], true );

if( isset( $postData['tool'] ) ) {	
	
	switch( $postData['tool'] ) {
		
		// Perform a change permission operation
		// for a single file 
		case 'changePermission' :
			
			$results = array( );
			if( lib\Session::isLoggedIn( ) && isset( $postData['fileID'] ) && isset( $postData['filePermission'] ) && isset( $postData['fileGroups'] )) {
				
				$fileHandler = new models\FileHandler( );
				if( $fileHandler->changePermission( $postData['fileID'], $postData['filePermission'], $postData['fileGroups'] ) ) {
					$results = array( "STATUS" => "success", "MESSAGE" => "File Permissions Successfully Changed!" );
				} else {
					$results = array( "STATUS" => "error", "MESSAGE" => "Unable to Change File Permissions. Please Try Again Later." );
				}
				
			} else {
				$results = array( "STATUS" => "error", "MESSAGE" => "Unable to Change File Permissions. Please Try Again Later." );
			}
			
			echo json_encode( $results );
			break;
			
		// Fetch a nice layout of privacy details
		// to show in a popup
		case 'fetchFilePrivacyDetails' :
			$fileHandler = new models\FileHandler( );
			$results = $fileHandler->fetchFormattedFilePrivacyDetails( $postData['fileID'] );
			echo json_encode( array( "DATA" => $results ));
			break;
			
	}
}

exit( );
 
?>