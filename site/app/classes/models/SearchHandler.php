<?php

namespace ORCA\app\classes\models;

/**
 * Search Handler
 * This class is for handling processing of data
 * for both global and advanced search queries
 */

use \PDO;
use ORCA\app\lib;
use ORCA\app\classes\models;
 
class SearchHandler {

	private $db;
	private $twig;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$loader = new \Twig_Loader_Filesystem( TEMPLATE_PATH );
		$this->twig = new \Twig_Environment( $loader );
	}
	
	/**
	 * Fetch a set of fields to be used for creating an advanced search
	 * output to display
	 */
	 
	public function buildAdvancedSearchFields( $columns ) {
		
		$searchFields = array( );
		
		foreach( $columns as $columnIndex => $columnDef ) {
			if( $columnDef['searchable'] ) {
				$view = "advancedSearch" . DS . "AdvancedSearch" . $columnDef['searchType'] . ".tpl";
		
				$field = $this->twig->render( $view, array(
					"TITLE" => $columnDef['searchName'],
					"COLUMN" => $columnIndex
				));
				
				$searchFields[] = $field;
			}
		}
		
		return $searchFields;
		
	}
	
	/**
	 * Build out the global search section of a query
	 * based on the passed in parameters
	 */
	 
	public function buildGlobalSearch( $params, $columns ) {
		
		
		$options = array( );
		$queryBits = array( );
		
		// Only add global search params if the main search passed in
		// from datatables contains a value
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			
			// For OR searches
			$searchValues = explode( "|", $params['search']['value'] );
			
			foreach( $columns as $columnIndex => $columnInfo ) {
				
				// Only allow it if the columns is set to "SEARCHABLE"
				$components = array( );
				if( $columnInfo['searchable'] ) {
					foreach( $columnInfo['searchCols'] as $colName => $searchType ) {
						
						switch( strtoupper( $searchType ) ) {
							case "EXACT" :
								foreach( $searchValues as $searchValue ) {
									$searchInfo = $this->convertWildcardSearchValue( $searchValue );
									if( $searchInfo['TYPE'] == "wildcard" ) {
										$components[] = $colName . " LIKE ?";
										$options[] = $searchInfo['VALUE'] . '%';
									} else {
										$components[] = $colName . "=?";
										$options[] = $searchInfo['VALUE'];
									}
									
								}
								break;
								
							case "RANGE" :
								foreach( $searchValues as $searchValue ) {
									$searchInfo = $this->convertWildcardSearchValue( $searchValue );
									if( is_numeric( $searchInfo['VALUE'] ) ) {
										$components[] = $colName . " BETWEEN ? AND ?";
										$options[] = $searchValue - 0.000005;
										$options[] = $searchValue + 0.000005;
										
									}
								}
								break;
								
							case "LIKE" :
								foreach( $searchValues as $searchValue ) {
									$searchInfo = $this->convertWildcardSearchValue( $searchValue );
									$components[] = $colName . " LIKE ?";
									$options[] = '%' . $searchInfo['VALUE'] . '%';
								}
								break;
						}
					}
					
					if( sizeof( $components ) > 0 ) {
						$queryBits[] = "(" . implode( " OR ", $components ) . ")";
					}
					
				}
				
			}
		
		}
		
		return array( "QUERY" => $queryBits, "OPTIONS" => $options );
		
	}
	
	/**
	 * Convert a search value into a wildcard if it
	 * contains a * at the end of it
	 */
	 
	public function convertWildcardSearchValue( $value ) {
		
		$value = trim( $value );
		$type = "normal";
		if( substr( $value, -1 ) === "*" ) {
			$value = rtrim( $value, "*" );
			$type = "wildcard";
		}
			
		return array( "VALUE" => $value, "TYPE" => $type );
		
	}
	
	/**
	 * Build the ORDER BY component of the query
	 * based on passed in parameters
	 */
	 
	public function buildOrderBy( $params, $columns ) {
		
		$orderByEntries = array( );
		if( isset( $params['order'] ) && sizeof( $params['order'] ) > 0 ) {
			
			$orderByEntries = array( );
			foreach( $params['order'] as $orderIndex => $orderInfo ) {
				$orderByEntries[] = $columns[$orderInfo['column']]['dbCol'] . " " . $orderInfo['dir'];
			}
			
		}
		
		if( sizeof( $orderByEntries ) > 0 ) {
			return " ORDER BY " . implode( ",", $orderByEntries );
		}
		
		return false;
		
	}
	
	/**
	 * Build the LIMIT component of the query
	 * based on passed in parameters
	 */
	 
	public function buildLimit( $params ) {
		return " LIMIT " . $params['start'] . "," . $params['length'];
	}
}