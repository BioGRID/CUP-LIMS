<?php

namespace ORCA\app\classes\models;

/**
 * Files
 * This class is for handling processing of data
 * for files and related tables.
 */

use \PDO;
 
class Files {

	private $db;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}
	
	/** 
	 * Insert a new file into the database if one with the same
	 * name doesn't already exist. Also move file into proper new
	 * home on the file system.
	 */
	 
	public function addFile( $expID, $expCode, $filename ) {
		
		// See if one with the same name already exists
		$stmt = $this->db->prepare( "SELECT file_id FROM " . DB_MAIN . ".files WHERE file_name=? AND experiment_id=? LIMIT 1" );
		$stmt->execute( array( $filename, $expID ));
		
		// If it exists, return an error
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			return $row->file_id;
		}
		
		try {
			
			// Move File
			if( $fileInfo = $this->moveFileToProcessing( $filename, $expCode )) {
		
				// Create File
				$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".files VALUES( '0', ?, ?, NOW( ), 'new', 'active', ?, ? )" );
				$stmt->execute( array( $filename, $fileInfo['SIZE'], $expID, $_SESSION[SESSION_NAME]['ID'] ));
				
				// Fetch its new ID
				$fileID = $this->db->lastInsertId( );
				
				// return new file ID
				return $fileID;
				
			}
			
		} catch( Exception $e ) {
			echo $e->getMessage( );
		}
		
		return false;
		
	}
	
	/**
	 * Move a file from the staging area to the processed area
	 */
	 
	private function moveFileToProcessing( $filename, $expCode ) {
		
		try {
			
			$oldDir = UPLOAD_TMP_PATH . DS . $expCode;
			$newDir = UPLOAD_PROCESSED_PATH . DS . $expCode;
			
			// If directory doesn't exist, create it first
			if( !is_dir( $newDir )) {
				mkdir( $newDir, 0777, false );
			}
			
			// Test to see if file exists
			if( file_exists( $oldDir . DS . $filename )) {
				if( rename( $oldDir . DS . $filename, $newDir . DS . $filename )) {
					$fileSize = filesize( $newDir . DS . $filename );
					return array( "SIZE" => $fileSize, "PATH" => $newDir . DS . $filename );
				}
			}
				
		} catch( Exception $e ) {
			echo $e->getMessage( );
		}
		
		return false;
	}
	
}

?>