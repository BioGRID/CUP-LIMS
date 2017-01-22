<?php

namespace ORCA\app\classes\models;

/**
 * View Handler
 * This class is for handling processing of data
 * for different views
 */

use \PDO;
use ORCA\app\lib;
 
class ViewHandler {

	private $db;
	private $twig;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$loader = new \Twig_Loader_Filesystem( TEMPLATE_PATH );
		$this->twig = new \Twig_Environment( $loader );
	}
	
	/** 
	 * Insert a new view into the database if one with the same
	 * options doesn't already exist and isn't out of date. 
	 */
	 
	public function addView( $files, $typeID, $valueID ) {
		
		// Make sure files are always listed in the same order
		sort( $files, SORT_NUMERIC );
		
		// See if one with the same parameters already exists
		// and is new enough to not have been pruned
		$stmt = $this->db->prepare( "SELECT view_id, view_code FROM " . DB_MAIN . ".views WHERE view_type_id=? AND view_value_id=? AND view_files=? AND view_status='active' LIMIT 1" );
		$stmt->execute( array( $typeID, $valueID, implode( "|", $files ) ));
		
		// If it exists, return the view code and update
		// the last viewed status
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			$this->updateLastViewed( $row->view_id );
			return array( "ID" => $row->view_id, "CODE" => $row->view_code );
		}
		
		// Build View
		// Add to view table immediately, so that
		// we can keep watching for the process to be
		// completed
		
		// a unique name for the table
		// so we don't accidentally overlap onto other tables
		$viewCode = uniqid( );
		
		$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".views VALUES( '0', ?, ?, ?, ?, ?, '0000-00-00 00:00:00', NOW( ), 'building', 'active', ? )" );
		$stmt->execute( array( $viewCode, $typeID, $valueID, implode( "|", $files ), "summary", $_SESSION[SESSION_NAME]['ID'] ));
		return array( "ID" => $this->db->lastInsertId( ), "CODE" => $viewCode );
		
	}
	
	/** 
	 * Change the last viewed date for a view
	 * the view_lastviewed parameter is used to determine
	 * which files are likely to be pruned
	 */
	
	public function updateLastViewed( $viewID ) {
		
		$stmt = $this->db->prepare( "UPDATE " . DB_MAIN . ".views SET view_lastviewed=NOW( ) WHERE view_id=?" );
		$stmt->execute( array( $viewID ) );
		return true;
		
	}
	
	/** 
	 * Change the view state for a specific view
	 */
	
	public function updateViewState( $viewID, $state ) {
		
		$stmt = $this->db->prepare( "UPDATE " . DB_MAIN . ".views SET view_state=? WHERE view_id=?" );
		$stmt->execute( array( $state, $viewID ) );
		return true;
		
	}
	
	/** 
	 * Get the view state for a view
	 */
	
	public function fetchViewState( $viewID ) {
		
		$stmt = $this->db->prepare( "SELECT view_state FROM " . DB_MAIN . ".views WHERE view_id=?" );
		$stmt->execute( array( $viewID ) );
		
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			return $row->view_state;
		}
		
		return false;
		
	}
	
	/** 
	 * Change the view status for a specific view
	 */
	
	public function updateViewStatus( $viewID, $status ) {
		
		$stmt = $this->db->prepare( "UPDATE " . DB_MAIN . ".views SET view_status=? WHERE view_id=?" );
		$stmt->execute( array( $state, $viewID ) );
		return true;
		
	}
	
	
}

?>