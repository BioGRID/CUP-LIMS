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
	
	public function addView( $viewName, $viewDesc, $typeID, $valueID, $files ) {
		
		$fileSet = array( );
		foreach( $files as $fileInfo ) {
			$fileSet[$fileInfo['fileID']] = $fileInfo['backgroundID'];
		}
		
		// Make sure files are always listed in the same order
		ksort( $fileSet, SORT_NUMERIC );
		$fileSet = json_encode( $fileSet );
		
		// See if one with the same parameters already exists
		// and is new enough to not have been pruned
		$stmt = $this->db->prepare( "SELECT view_id, view_code FROM " . DB_MAIN . ".views WHERE view_type_id=? AND view_value_id=? AND view_files=? AND view_status='active' LIMIT 1" );
		$stmt->execute( array( $typeID, $valueID, $fileSet ));
		
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
		$emptyArray = json_encode( array( ) );
		
		$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".views VALUES( '0', ?, ?, ?, ?, ?, ?, ?, '0000-00-00 00:00:00', NOW( ), 'building', 'active', ?, ?, ? )" );
		$stmt->execute( array( $viewName, $viewDesc, $viewCode, $typeID, $valueID, $fileSet, "summary", $emptyArray, $emptyArray, $_SESSION[SESSION_NAME]['ID'] ));
		return array( "ID" => $this->db->lastInsertId( ), "CODE" => $viewCode );
		
	}
	
	/**
	 * Fetch list of View Types
	 */
	 
	public function fetchViewTypes( ) {
		$stmt = $this->db->prepare( "SELECT view_type_id, view_type_name FROM " . DB_MAIN . ".view_types WHERE view_type_status='active' ORDER BY view_type_name ASC" );
		$stmt->execute( );
		
		$viewTypes = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
			$viewTypes[$row->view_type_id] = $row->view_type_name;
		}
		
		return $viewTypes;
	}
	
	/**
	 * Fetch list of View Values
	 */
	 
	public function fetchViewValues( ) {
		$stmt = $this->db->prepare( "SELECT view_value_id, view_value_name FROM " . DB_MAIN . ".view_values WHERE view_value_status='active' ORDER BY view_value_name ASC" );
		$stmt->execute( );
		
		$viewValues = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
			$viewValues[$row->view_value_id] = $row->view_value_name;
		}
		
		return $viewValues;
	}
	
	/**
	 * Fetch view information out of the database
	 */
	 
	public function fetchView( $viewID ) {
		$stmt = $this->db->prepare( "SELECT * FROM " . DB_MAIN . ".views WHERE view_id=?" );
		$stmt->execute( array( $viewID ) );
		
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			return $row;
		}
		
		return false;
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