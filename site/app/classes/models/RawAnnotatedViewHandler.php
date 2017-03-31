<?php

namespace ORCA\app\classes\models;

/**
 * Raw Annotated View Handler
 * This class is for handling processing of raw data
 * from a raw annoted view table
 */

use \PDO;
use ORCA\app\classes\models;
 
class RawAnnotatedViewHandler {

	private $db;
	private $viewHandler;
	private $view;
	private $searchHandler;

	public function __construct( $viewID ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$this->viewHandler = new models\ViewHandler( );
		$this->view = $this->viewHandler->fetchView( $viewID );
		$this->searchHandler = new models\SearchHandler( );
	}
	
	/**
	 * Fetch column headers for a Raw Reads listing DataTable
	 */
	 
	 public function fetchColumnDefinitions( ) {
	 
		$columns = array( );
		$columns[0] = array( "title" => "sgRNA", "data" => 0, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'sgrna_sequence', "searchable" => true, "searchType" => "Text", "searchName" => "sgRNA", "searchCols" => array( "sgrna_sequence" => "exact" ));
		$columns[1] = array( "title" => "Names", "data" => 1, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => "group_names", "searchable" => true, "searchType" => "Text", "searchName" => "Names", "searchCols" => array( "group_names" => "exact" ));
		$columns[2] = array( "title" => "Raw Reads", "data" => 2, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'raw_read_count', "searchable" => true, "searchType" => "NumericRange", "searchName" => "Name", "searchCols" => array( "raw_read_count" => "range" ));
		
		return $columns;
		
	}
	
	/**
	 * Fetch Raw Reads results formatted correctly as rows for DataTable display
	 */
	 
	 public function buildRows( $params ) {
		
		$rawReadList = $this->buildCustomizedRowList( $params );
		$rows = array( );
		foreach( $rawReadList as $rawReadID => $rawInfo ) {
			$column = array( );
			
			$column[] = $rawInfo->sgrna_sequence;
			
			$column[] = implode( ", ", explode( "|", $rawInfo->group_names ));
			$column[] = $rawInfo->raw_read_count;
		
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/**
	 * Build a base query with search params
	 * for DataTable construction
	 */
	 
	private function buildDataTableQuery( $params, $columns, $countOnly = false ) {
		
		$query = "SELECT ";
		if( $countOnly ) {
			$query .= " count(*) as rowCount";
		} else {
			$query .= " raw_read_id, sgrna_id, sgrna_sequence, sgrna_group_ids, group_names, raw_read_count";
		}
		
		$query .= " FROM " . DB_VIEWS . ".view_" . $this->view->view_code;
		
		// Main storage for Query Components
		$queryEntries = array( );
		$options = array( );
		
		// Add in global search filter terms
		$globalQuery = $this->searchHandler->buildGlobalSearch( $params, $columns );
		if( sizeof( $globalQuery['QUERY'] ) > 0 ) {
			$queryEntries[] = "(" . implode( " OR ", $globalQuery['QUERY'] ) . ")";
			$options = array_merge( $options, $globalQuery['OPTIONS'] );
		}
		
		// Add in advanced search filter terms
		$advancedQuery = $this->searchHandler->buildAdvancedSearch( $params, $columns );
		if( sizeof( $advancedQuery['QUERY'] ) > 0 ) {
			$queryEntries[] = "(" . implode( " AND ", $advancedQuery['QUERY'] ) . ")";
			$options = array_merge( $options, $advancedQuery['OPTIONS'] );
		}
		
		// Check for actual entries here
		// so we only add WHERE component if necessary
		if( sizeof( $queryEntries ) > 0 ) {
			$query .= " WHERE " . implode( " AND ", $queryEntries );
		}
		
		return array( "QUERY" => $query, "OPTIONS" => $options );
			
	}
	
	/**
	 * Build a set of raw reads data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function buildCustomizedRowList( $params ) {
		
		$columns = $this->fetchColumnDefinitions( );
		
		$rows = array( );
		
		$queryInfo = $this->buildDataTableQuery( $params, $columns, false );
		$query = $queryInfo['QUERY'];
		$options = $queryInfo['OPTIONS'];
		
		$orderBy = $this->searchHandler->buildOrderBy( $params, $columns );
		if( $orderBy ) {
			$query .= $orderBy;
		}
		
		$query .= $this->searchHandler->buildLimit( $params );
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$rows[$row->raw_read_id] = $row;
		}
		
		return $rows;
		
	}
	
	/**
	 * Build a count of raw reads data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function getUnfilteredRowCount( $params ) {
		
		$columns = $this->fetchColumnDefinitions( );
		
		$queryInfo = $this->buildDataTableQuery( $params, $columns, true );
		$query = $queryInfo['QUERY'];
		$options = $queryInfo['OPTIONS'];
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->rowCount;
		
	}
	
	/**
	 * Get a count of all raw reads available
	 */
	 
	public function fetchRowCount( $fileID ) {
		
		$stmt = $this->db->prepare( "SELECT COUNT(*) as rowCount FROM " . DB_VIEWS . ".view_" . $this->view->view_code );
		$stmt->execute( array( $fileID ) );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->rowCount;
		
	}
	
	/**
	 * Fetch a set of buttons for the raw reads Datatable toolbar
	 */
	
	public function fetchToolbar( ) {
		
		$buttons = array( );
		
		return implode( "", $buttons );
		
	}
	
}

?>