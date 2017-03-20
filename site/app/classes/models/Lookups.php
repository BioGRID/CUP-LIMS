<?php

namespace ORCA\app\classes\models;

/**
 * Lookups
 * This class is for creating quick lookup hashes
 * that can be used to speed up various operations
 * and limit SQL connections required. It also contains functions
 * for looking up annotation table details.
 */

use \PDO;
 
class Lookups {

	private $db;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}
	
	/**
	 * Build a list of all sgRNAs and their associated ids
	 */
	 
	public function buildSGRNAHash( ) {
		
		$sgRNAs = array( );
		
		$stmt = $this->db->prepare( "SELECT sgrna_id, sgrna_sequence FROM " . DB_MAIN . ".sgRNAs" );
		$stmt->execute( );
		
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$sgRNAs[$row->sgrna_sequence] = $row->sgrna_id;
		}
		
		return $sgRNAs;
	}
	
	/**
	 * Build a list of cell lines that can be referenced by ID
	 * and are ordered by name ASC
	 */
	 
	public function buildCellLineHash( ) {
		
		$cellLines = array( );
		
		$stmt = $this->db->prepare( "SELECT cell_line_id, cell_line_name FROM " . DB_MAIN . ".cell_lines WHERE cell_line_status='active' ORDER BY cell_line_name ASC" );
		$stmt->execute( );
		
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$cellLines[$row->cell_line_id] = $row->cell_line_name;
		}
		
		return $cellLines;
		
	}
	
	/**
	 * Build a list of organisms that can be referenced by ID
	 * and are ordered by name ASC
	 */
	 
	public function buildOrganismHash( ) {
		
		$organisms = array( );
		
		$stmt = $this->db->prepare( "SELECT organism_id, organism_official_name, organism_strain FROM " . DB_MAIN . ".organisms ORDER BY organism_official_name ASC" );
		$stmt->execute( );
		
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			
			$organismName = $row->organism_official_name;
			if( $row->organism_strain != "-" ) {
				$organismName .= " (" . $row->organism_strain . ")";
			}
			
			$organisms[$row->organism_id] = $organismName;
		}
		
		return $organisms;
		
	}
	
}

?>