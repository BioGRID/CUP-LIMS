<?php

namespace ORCA\app\classes\models;

/**
 * Raw Data Handler
 * This class is for handling processing of raw data
 * inside files
 */

use \PDO;
 
class RawDataHandler {

	private $db;
	private $sgHASH;

	public function __construct( $sgHASH ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->sgHASH = $sgHASH;
	}
	
	/**
	 * Process a two column tab-delimited file with the layout:
	 * READ COUNT <tab> sgRNA
	 */
	 
	public function processTwoColumnTabFile( ) {
		
		
		
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
	
}

?>