<?php

namespace ORCA\app\classes\models;

/**
 * Raw Reads Handler
 * This class is for handling processing of raw data
 * from the raw_reads table
 */

use \PDO;
 
class RawReadsHandler {

	private $db;
	private $sgHASH;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}
	
	/**
	 * Fetch column headers for a Raw Reads listing DataTable
	 */
	 
	 public function fetchColumnDefinitions( ) {
	 
		$columns = array( );
		$columns[0] = array( "title" => "sgRNA", "data" => 0, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'sgrna_sequence' );
		$columns[1] = array( "title" => "Raw Reads", "data" => 1, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'raw_read_count' );
		$columns[2] = array( "title" => "Options", "data" => 2, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
		
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
			$column[] = $rawInfo->raw_read_count;
			$column[] = "-";
		
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/**
	 * Build a base query with search params
	 * for DataTable construction
	 */
	 
	private function buildDataTableQuery( $params, $countOnly = false ) {
		
		$query = "SELECT ";
		if( $countOnly ) {
			$query .= " count(*) as rowCount";
		} else {
			$query .= " rr.raw_read_id, rr.sgrna_id, rr.raw_read_count, sg.sgrna_sequence";
		}
		
		$query .= " FROM " . DB_MAIN . ".raw_reads rr LEFT JOIN sgRNAs sg ON (rr.sgrna_id = sg.sgrna_id)";
		
		$options = array( $params['fileID'] );
		$query .= " WHERE sgrna_status='active' AND file_id=?";
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " AND (sgrna_sequence LIKE ? OR raw_read_count=?)";
			array_push( $options, $params['search']['value'] . '%', $params['search']['value'] );
		}
		
		return array( "QUERY" => $query, "OPTIONS" => $options );
			
	}
	
	/**
	 * Build a set of raw reads data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function buildCustomizedRowList( $params ) {
		
		$columnSet = $this->fetchColumnDefinitions( );
		
		$rows = array( );
		
		$queryInfo = $this->buildDataTableQuery( $params, false );
		$query = $queryInfo['QUERY'];
		$options = $queryInfo['OPTIONS'];
		
		if( isset( $params['order'] ) && sizeof( $params['order'] ) > 0 ) {
			$query .= " ORDER BY ";
			$orderByEntries = array( );
			foreach( $params['order'] as $orderIndex => $orderInfo ) {
				$orderByEntries[] = $columnSet[$orderInfo['column']]['dbCol'] . " " . $orderInfo['dir'];
			}
			
			$query .= implode( ",", $orderByEntries );
		}
		
		$query .= " LIMIT " . $params['start'] . "," . $params['length'];
		
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
		
		$queryInfo = $this->buildDataTableQuery( $params, true );
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
		
		$stmt = $this->db->prepare( "SELECT COUNT(*) as rowCount FROM " . DB_MAIN . ".raw_reads WHERE file_id=?" );
		$stmt->execute( array( $fileID ) );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->rowCount;
		
	}
	
	/**
	 * Fetch a set of buttons for the raw reads Datatable toolbar
	 */
	
	public function fetchToolbar( ) {
		
		$buttons = array( );
		
		// if( lib\Session::validateCredentials( lib\Session::getPermission( 'VIEW FILES' )) ) {
			// $view = "blocks" . DS . "ORCADataTableToolbarButton.tpl";
			// $buttons[] = $this->twig->render( $view, array( 
				// "BTN_CLASS" => "btn-info experimentViewFilesBtn",
				// "BTN_LINK" => "",
				// "BTN_ID" => "experimentViewFilesBtn",
				// "BTN_ICON" => "fa-file-text",
				// "BTN_TEXT" => "View Files"
			// ));
		// }
		
		// if( lib\Session::validateCredentials( lib\Session::getPermission( 'CREATE VIEW' )) ) {
			// $view = "blocks" . DS . "ORCADataTableToolbarButton.tpl";
			// $buttons[] = $this->twig->render( $view, array( 
				// "BTN_CLASS" => "btn-orca2 experimentCreateViewBtn",
				// "BTN_LINK" => "",
				// "BTN_ID" => "experimentCreateViewBtn",
				// "BTN_ICON" => "fa-bar-chart",
				// "BTN_TEXT" => "Create View"
			// ));
		// }
		
		// if( lib\Session::validateCredentials( lib\Session::getPermission( 'MANAGE EXPERIMENTS' )) ) {
			// $view = "blocks" . DS . "ORCADataTableToolbarDropdown.tpl";
			// $buttons[] = $this->twig->render( $view, array(
				// "BTN_CLASS" => "btn-danger",
				// "BTN_ICON" => "fa-cog",
				// "BTN_TEXT" => "Tools",
				// "LINKS" => array(
					// "experimentDisableChecked" => array( "linkHREF" => "", "linkText" => "Disable Checked Experiments", "linkClass" => "experimentDisableChecked" )
				// )
			// ));
		// }
		
		return implode( "", $buttons );
		
	}
	
}

?>