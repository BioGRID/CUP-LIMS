<?php

namespace ORCA\app\classes\models;

/**
 * Experiments
 * This class is for handling processing of data
 * for experiments and related tables.
 */

use \PDO;
use ORCA\app\classes\models;
 
class Experiments {

	private $db;
	private $files;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$this->files = new models\Files( );
	}
	
	/** 
	 * Insert an experiment into the database if one with the same
	 * name doesn't already exist.
	 */
	 
	public function insertExperiment( $data ) {
		
		// See if one with the same name already exists
		$stmt = $this->db->prepare( "SELECT experiment_id FROM " . DB_MAIN . ".experiments WHERE experiment_name=? LIMIT 1" );
		$stmt->execute( array( $data->experimentName ));
		
		// If it exists, return an error
		if( $stmt->rowCount( ) > 0 ) {
			return array( "STATUS" => "error", "MESSAGE" => "An experiment with this name already exists, please use this one instead..." );
		}
		
		// Otherwise, begin insert process
		$this->db->beginTransaction( );
		
		try {
		
			// Create Experiment
			$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".experiments VALUES( '0', ?, ?, ?, ?, ?, NOW( ), ?, 'active', ? )" );
			$stmt->execute( array( $data->experimentName, $data->experimentDesc, $data->experimentCode, $data->experimentCell, $data->experimentDate, sizeof( $data->experimentFiles ), $_SESSION[SESSION_NAME]['ID'] ) );
			
			// Fetch its new ID
			$experimentID = $this->db->lastInsertId( );
			
			// Enter the list of files
			foreach( $data->experimentFiles as $file ) {
				$this->files->addFile( $experimentID, $data->experimentCode, $file );
			}
			
			$this->db->commit( );
			return array( "STATUS" => "success", "MESSAGE" => "Successfully Added Experiment", "ID" => $experimentID );
			
		} catch( PDOException $e ) {
			$this->db->rollback( );
			return array( "STATUS" => "error", "MESSAGE" => "Database Insert Problem. " . $e->getMessage( ) );
		}
		
	}
	
}

?>