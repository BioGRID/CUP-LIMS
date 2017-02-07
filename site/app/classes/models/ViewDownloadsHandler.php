<?php

namespace ORCA\app\classes\models;

/**
 * View Downloads Handler
 * This class is for handling processing of data
 * for the creation of a download file based on the view
 */

use \PDO;
use ORCA\app\lib;
use ORCA\app\classes\models;
 
class ViewDownloadsHandler {

	private $db;

	public function __construct( $viewID ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$this->viewHandler = new models\ViewHandler( );
		$this->view = $this->viewHandler->fetchView( $viewID );
	}
	
	/**
	 * Fetch view download header
	 */
	 
	private function fetchViewDownloadHeader( ) {
		
		if( $this->view->view_type_id == "2" ) {
			// Raw Annotated
			return "#sgRNA\tNames\tReads";
		} else if( $this->view->view_type_id == "1" ) {
			// Matrix View
			$fields = array( );
			$fields[] = "group_id";
			$fields[] = "group_name";
			$fields[] = "official_symbol";
			$fields[] = "systematic_name";
			$fields[] = "aliases";
			$fields[] = "definition";
			$fields[] = "organism_id";
			$fields[] = "organism_official_name";
			
			$conditionCols = json_decode( $this->view->view_details, true );
			ksort( $conditionCols['FILES'], SORT_NATURAL );
			
			foreach( $conditionCols['FILES'] as $conditionName => $fileInfo ) {
				$fields[] = $fileInfo['FILE']['NAME'] . " (" . $fileInfo['BG']['NAME'] . ")";
			}
			
			return "#" . implode( "\t", $fields );
		}
		
		return "";
		
	}
	
	/**
	 * Fetch and output the rows for this file
	 */
	 
	public function outputRows( ) {
		
		$header = $this->fetchViewDownloadHeader( );
		echo $header . "\n";
		
		if( $this->view->view_type_id == "2" ) {
			// Raw Annotated
			$this->outputRawAnnotatedViewRows( );
		} else if( $this->view->view_type_id == "1" ) {
			// Matrix View
			$this->outputMatrixViewRows( );
		}
		
	}
	
	/**
	 * Output Raw Annotated View Rows
	 */
	 
	private function outputRawAnnotatedViewRows( ) {
		
		$stmt = $this->db->prepare( "SELECT sgrna_sequence, group_names, raw_read_count FROM " . DB_VIEWS . ".view_" . $this->view->view_code );
		$stmt->execute( );
		
		while( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			echo implode( "\t", $row ) . "\n";
		}
		
	}
	
	/**
	 * Output Matrix View Rows
	 */
	 
	private function outputMatrixViewRows( ) {
		
		$conditionCols = json_decode( $this->view->view_details, true );
		ksort( $conditionCols['FILES'], SORT_NATURAL );
		$extraCols = array_keys( $conditionCols['FILES'] );
		
		$stmt = $this->db->prepare( "SELECT sgrna_group_id, group_name, official_symbol, systematic_name, aliases, definition, organism_id, organism_official_name, " . implode( ",", $extraCols ) . " FROM " . DB_VIEWS . ".view_" . $this->view->view_code );
		$stmt->execute( );
		
		while( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			echo implode( "\t", $row ) . "\n";
		}
		
	}
	
}

?>