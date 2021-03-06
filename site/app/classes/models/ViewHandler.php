<?php

namespace ORCA\app\classes\models;

/**
 * View Handler
 * This class is for handling processing of data
 * for different views
 */

use \PDO;
use ORCA\app\lib;
use ORCA\app\classes\models;
 
class ViewHandler {

	private $db;
	private $twig;
	private $searchHandler;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$loader = new \Twig_Loader_Filesystem( TEMPLATE_PATH );
		$this->twig = new \Twig_Environment( $loader );
		$this->searchHandler = new models\SearchHandler( );
	}
	
	/** 
	 * Insert a new view into the database if one with the same
	 * options doesn't already exist and isn't out of date. 
	 */
	
	public function addView( $viewName, $viewDesc, $typeID, $valueID, $files, $permission, $groups ) {
		
		$fileSet = array( );
		$mappingSet = array( );
		foreach( $files as $fileInfo ) {
			$fileSet[$fileInfo['fileID']] = array( "BG" => $fileInfo['backgroundID'], "MAP" => $fileInfo['mappingID'] );
			$mappingSet[] = $fileInfo['mappingID'];
		}
		
		// Unique and turn to json
		$mappingSet = array_values( array_unique( $mappingSet ));
		$mappingSet = json_encode( $mappingSet );
		
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
		
		$groupVal = array( );
		if( sizeof( $groups ) > 0 ) {
			$groupVal = $groups;
		}
				
		$groupVal = json_encode( $groupVal );
		
		$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".views VALUES( '0', ?, ?, ?, ?, ?, ?, ?, ?, '0000-00-00 00:00:00', NOW( ), 'building', 'active', ?, ?, ?, ?, ? )" );
		$stmt->execute( array( $viewName, $viewDesc, $viewCode, $typeID, $valueID, $fileSet, $mappingSet, "summary", $emptyArray, $emptyArray, $_SESSION[SESSION_NAME]['ID'], $permission, $groupVal ));
		
		if( CONFIG['VIEWGENERATOR']['ACTIVE'] ) {
			// Run Active View Generator Service
			$this->callViewGeneratorService( );
		}
		
		return array( "ID" => $this->db->lastInsertId( ), "CODE" => $viewCode );
		
	}
	
	/**
	 * Call View Generator Service to tell it to start a view building
	*/
	
	private function callViewGeneratorService( ) {
	
		$curl = curl_init( );
		curl_setopt( $curl, CURLOPT_URL, CONFIG['VIEWGENERATOR']['HOST'] . ":" . CONFIG['VIEWGENERATOR']['PORT'] . "/View" );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		
		$result = curl_exec( $curl );
		curl_close( $curl );
		
		return $result;
	
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
	 * Fetch a view type name when given a view type ID
	 */
	 
	public function fetchViewTypeName( $viewTypeID ) {
		
		$stmt = $this->db->prepare( "SELECT view_type_name FROM " . DB_MAIN . ".view_types WHERE view_type_id=? LIMIT 1" );
		$stmt->execute( array( $viewTypeID ));
		
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			return $row->view_type_name;
		}
		
		return false;
		
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
	 * Fetch a view value name when given a view value ID
	 */
	 
	public function fetchViewValueName( $viewValueID ) {
		
		$stmt = $this->db->prepare( "SELECT view_value_name FROM " . DB_MAIN . ".view_values WHERE view_value_id=? LIMIT 1" );
		$stmt->execute( array( $viewValueID ));
		
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			return $row->view_value_name;
		}
		
		return false;
		
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
	
	/**
	 * Fetch column headers for an View listing DataTable
	 */
	 
	 public function fetchColumnDefinitions( ) {
	 
		$columns = array( );
		$columns[0] = array( "title" => "", "data" => 0, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '', "searchable" => false );
		$columns[1] = array( "title" => "Name", "data" => 1, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'view_title', "searchable" => true, "searchType" => "Text", "searchName" => "Name", "searchCols" => array( "view_title" => "exact" ));
		$columns[2] = array( "title" => "Desc", "data" => 2, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'view_desc', "searchable" => true, "searchType" => "Text", "searchName" => "Desc", "searchCols" => array( "view_desc" => "exact" ));
		$columns[3] = array( "title" => "Type", "data" => 3, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'view_type_name', "searchable" => true, "searchType" => "Text", "searchName" => "Type", "searchCols" => array( "view_type_name" => "exact" ));
		$columns[4] = array( "title" => "Values", "data" => 4, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'view_value_name', "searchable" => true, "searchType" => "Text", "searchName" => "Values", "searchCols" => array( "view_value_name" => "exact" ));
		$columns[5] = array( "title" => "Run Date", "data" => 5, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'view_addeddate', "searchable" => true, "searchType" => "Date", "searchName" => "Run Date", "searchCols" => array( "view_addeddate" => "date" ));
		$columns[6] = array( "title" => "Files", "data" => 6, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'view_files', "searchable" => false );
		$columns[7] = array( "title" => "State", "data" => 7, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'view_state', "searchable" => true, "searchType" => "Text", "searchName" => "State", "searchCols" => array( "view_state" => "exact" ));
		$columns[8] = array( "title" => "Privacy", "data" => 8, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'view_permission', "searchable" => true, "searchType" => "Text", "searchName" => "Privacy", "searchCols" => array( "view_permission" => "exact" ));
		$columns[9] = array( "title" => "User", "data" => 9, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'user_name', "searchable" => true, "searchType" => "Text", "searchName" => "User", "searchCols" => array( "user_name" => "exact" ));
		
		return $columns;
		
	}
	
	/**
	 * Fetch view results formatted correctly as rows for DataTable display
	 */
	 
	 public function buildViewRows( $params ) {
		
		$viewList = $this->buildCustomizedViewList( $params );
		$rows = array( );
		foreach( $viewList as $viewID => $viewInfo ) {
			$column = array( );
			
			$checkedBoxes = array( );
			if( isset( $params['checkedBoxes'] )) {
				$checkedBoxes = $params['checkedBoxes'];
			}
			
			if( isset( $checkedBoxes[$viewID] ) && $checkedBoxes[$viewID] ) {
				$column[] = "<input type='checkbox' class='orcaDataTableRowCheck' value='" . $viewID . "' checked />";
			} else {
				$column[] = "<input type='checkbox' class='orcaDataTableRowCheck' value='" . $viewID . "' />";
			}
			
			$column[] = "<a href='" . WEB_URL . "/View?viewID=" . $viewInfo->view_id . "' title='" . $viewInfo->view_title . "'>" . $viewInfo->view_title . "</a>";
			$column[] = $viewInfo->view_desc;
			$column[] = $viewInfo->view_type_name . " <i class='fa fa-lg primaryIcon " . $this->fetchViewTypeIcon( $viewInfo->view_type_id ) . "'></i>";
			$column[] = $viewInfo->view_value_name;
			$column[] = $viewInfo->view_addeddate;
			
			$fileSet = json_decode( $viewInfo->view_files );
			$fileList = array( );
			foreach( $fileSet as $fileID => $fileInfo ) {
				$backgroundSplit = explode( "|", $fileInfo->BG );
				$fileList[] = $fileID;
				foreach( $backgroundSplit as $bgID ) {
					if( $bgID != "0" ) {
						$fileList[] = $bgID;
					}
				}
			}
			
			$fileList = array_unique( $fileList );
			$column[] = "[<a href='" . WEB_URL . "/Files?fileIDs=" . implode( "|", $fileList ) . "' title='View " . sizeof( $fileList ) . " Files'>View " . sizeof( $fileList ) . " Files</a>]";
			
			$column[] = $this->generateViewState( $viewInfo );
			$column[] = $this->formatPermission( $viewInfo );
			
			$column[] = $viewInfo->user_name;
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/**
	 * Return the original passed in query with attached permissions
	 * data
	 */
	 
	private function buildPermissionQuery( $query, $prepend = "" ) {
		
		// Check for valid permissions to access
		if( $prepend != "" ) {
			$query .= " AND (" . $prepend . "user_id='" . $_SESSION[SESSION_NAME]['ID'] . "' OR " . $prepend . "view_permission='public'";
		} else {
			$query .= " AND (user_id='" . $_SESSION[SESSION_NAME]['ID'] . "' OR view_permission='public'";
		}
		
		// Add Group Check
		if( sizeof( $_SESSION[SESSION_NAME]['GROUPS'] ) > 0 ) {
			$groupIDs = array_keys( $_SESSION[SESSION_NAME]['GROUPS'] );
			if( $prepend != "" ) {
				$query .= " OR (" . $prepend . "view_groups LIKE '%\"" . implode( "\"%' OR " . $prepend . "view_groups LIKE '%\"", $groupIDs ) . "\"%'))";
			} else {
				$query .= " OR (view_groups LIKE '%\"" . implode( "\"%' OR view_groups LIKE '%\"", $groupIDs ) . "\"%'))";
			}
		} else {
			$query .= ")";
		}
		
		return $query;
		
	}
	
	/**
	 * Convert the view state into a more graphical representation
	 */
	 
	public function generateViewState( $viewInfo ) {
		
		if( $viewInfo->view_state == "complete" ) {
			return "<strong><span class='text-success'>" . $viewInfo->view_state . " <i class='fa fa-check'></i></span></strong>";
		}
		
		return "<strong><span class='text-danger'>" . $viewInfo->view_state . " <i class='fa fa-spin fa-spinner'></i></a></span></strong>"; 
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
			$query .= " view.*, vt.view_type_name, vv.view_value_name, u.user_name, u.user_firstname, u.user_lastname";
		}
		
		$query .= " FROM " . DB_MAIN . ".views view LEFT JOIN view_types vt ON (view.view_type_id = vt.view_type_id) LEFT JOIN view_values vv ON (view.view_value_id = vv.view_value_id) LEFT JOIN users u ON (view.user_id = u.user_id)";
		
		$options = array( );
		$query .= " WHERE view_status='active' AND view.view_type_id != '2'";
		
		// Main storage for Query Components
		$queryEntries = array( );
		
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
			$query .= " AND " . implode( " AND ", $queryEntries );
		}
		
		// Addon Permission Check Query Params
		$query = $this->buildPermissionQuery( $query, "view." );
		
		return array( "QUERY" => $query, "OPTIONS" => $options );
			
	}
	
	/**
	 * Build a set of view data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function buildCustomizedViewList( $params ) {
		
		$columns = $this->fetchColumnDefinitions( );
		
		$views = array( );
		
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
			$views[$row->view_id] = $row;
		}
		
		return $views;
		
	}
	
	/**
	 * Build a count of view data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function getUnfilteredViewCount( $params ) {
		
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
	 * Get a count of all views available
	 */
	 
	public function fetchViewCount( ) {
		
		$query = "SELECT COUNT(*) as viewCount FROM " . DB_MAIN . ".views WHERE view_id > 1";
		
		// Addon Permission Check Query Params
		$query = $this->buildPermissionQuery( $query );
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->viewCount;
		
	}
	
	/**
	 * Fetch a set of buttons for the view listing
	 * table toolbar
	 */
	
	public function fetchViewToolbar( ) {
		
		$buttons = array( );
		
		// if( lib\Session::validateCredentials( lib\Session::getPermission( 'MANAGE VIEWS' )) ) {
			// $view = "blocks" . DS . "ORCADataTableToolbarDropdown.tpl";
			// $buttons[] = $this->twig->render( $view, array(
				// "BTN_CLASS" => "btn-danger",
				// "BTN_ICON" => "fa-cog",
				// "BTN_TEXT" => "Tools",
				// "LINKS" => array(
					// "viewDisableChecked" => array( "linkHREF" => "", "linkText" => "Disable Checked Views", "linkClass" => "viewDisableChecked" )
				// )
			// ));
		// }
		
		return implode( "", $buttons );
		
	}
	
	/**
	 * Disable views specified by ID passed in as an array
	 */
	
	public function disableViews( $viewIDs ) {
		
		$querySet = array_fill( 0, sizeof( $viewIDs ), "?" );
		$stmt = $this->db->prepare( "UPDATE " . DB_MAIN . ".views SET view_status='inactive' WHERE view_id IN (" . implode( ",", $querySet ) . ")" );
		$stmt->execute( $viewIDs );
		
	}
	
	/**
	 * Fetch a formatted summary showing raw reads data
	 * stored for one specific file and group id
	 */
	 
	public function fetchRawReadsSummaryByGroupID( $fileID, $fileName, $groupID, $groupName, $scoreVal ) {
		
		$stmt = $this->db->prepare( "SELECT sgrna_id FROM " . DB_MAIN . ".sgRNA_group_mappings WHERE sgrna_group_id=? AND sgrna_group_mapping_status='active'" );
		$stmt->execute( array( $groupID ) );
		
		$sgrnaIDs = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$sgrnaIDs[] = $row->sgrna_id;
		}
		
		$querySet = array_fill( 0, sizeof( $sgrnaIDs ), "?" );
		$stmt = $this->db->prepare( "SELECT s.sgrna_sequence, r.raw_read_count FROM " . DB_MAIN . ".raw_reads r LEFT JOIN " . DB_MAIN . ".sgRNAs s ON (r.sgrna_id=s.sgrna_id) WHERE r.sgrna_id IN (" . implode( ",", $querySet ) . ") AND file_id=?" );
		$sgrnaIDs[] = $fileID;
		$stmt->execute( $sgrnaIDs );
		
		$rawReads = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$rawReads[$row->sgrna_sequence] = $row->raw_read_count;
		}
	
		$rawReadsTable = $this->twig->render( "view" . DS . "ViewRawReadsTable.tpl", array(
			"FILE_NAME" => $fileName,
			"GROUP_NAME" => $groupName,
			"RAW_READS" => $rawReads,
			"SCORE_VAL" => $scoreVal
		));
		
		return $rawReadsTable;
		
		
	}
	
	/** 
	 * Fetch a recent list of views limited by ID if not empty
	 */
	 
	public function fetchViewList( $userID = "", $limit = 5 ) {
		
		$viewValues = $this->fetchViewValues( );

		$options = array( );
		$query = "SELECT v.view_id, v.view_title, v.view_type_id, DATE_FORMAT( v.view_addeddate, '%Y-%m-%d'  ) as addedDate, v.view_state, v.view_permission, v.view_value_id, u.user_name FROM " . DB_MAIN . ".views v LEFT JOIN " . DB_MAIN . ".users u ON (v.user_id=u.user_id) WHERE v.view_type_id != '2'";
		if( $userID != "" ) {
			$options[] = $userID;
			$query .= " AND v.user_id=?";
		}
		
		$query = $this->buildPermissionQuery( $query, "v." );
		
		$query .= " ORDER BY v.view_addeddate DESC LIMIT " . $limit;
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		$views = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			
			$viewTypeIcon = $this->fetchViewTypeIcon( $row->view_type_id );
			
			$views[$row->view_id] = array( 
				"ID" => $row->view_id,
				"TITLE" => $row->view_title,
				"ADDED_DATE" => $row->addedDate,
				"TYPE_ICON" => $viewTypeIcon,
				"STATE" => $this->generateViewState( $row ),
				"VALUE" => $viewValues[$row->view_value_id],
				"PERMISSION" => $this->formatPermission( $row ),
				"USER_NAME" => $row->user_name
			);
		}
		
		return $views;
		
	}
	
	/**
	 * Convert a view type id into an icon representation
	 */
	 
	public function fetchViewTypeIcon( $viewTypeID ) {
		
		switch( $viewTypeID ) {
			
			# Matrix
			case "1" :
				return "fa-table";
			
			# Annotated Raw Data
			case "2" :
				return "fa-language";
		}
		
		return "fa-binoculars";
		
	}
	
	/**
	 * Fetch formatted group annotation to be displayed in a popup tooltip
	 */
	 
	public function fetchFormattedGroupAnnotation( $viewID, $groupID ) {
		
		$viewInfo = $this->fetchView( $viewID );
		
		$stmt = $this->db->prepare( "SELECT sgrna_group_reference, official_symbol, systematic_name, aliases, definition, organism_official_name, biogrid_id FROM " . DB_VIEWS . ".view_" . $viewInfo->view_code . " WHERE sgrna_group_id=? LIMIT 1" );
		
		$stmt->execute( array( $groupID ) );
		
		// If it exists, return an error
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			
			$annotationParams = array( );
			if( $row->official_symbol != "-" ) {
				$annotationParams["Official Symbol"] = $row->official_symbol;
			}
			
			if( $row->systematic_name != "-" ) {
				$annotationParams["Systematic Name"] = $row->systematic_name;
			}
			
			if( $row->aliases != "-" ) {
				$aliases = json_decode( $row->aliases, true );
				$annotationParams["Aliases"] = implode( ", ", $aliases );
			}
			
			if( $row->definition != "-" ) {
				$annotationParams["Definition"] = $row->definition;
			}
			
			if( $row->organism_official_name != "-" ) {
				$annotationParams["Organism"] = $row->organism_official_name;
			}
			
			$links = array( );
			$links["entrez"] = models\LinkoutGenerator::getLinkout( "entrez", $row->sgrna_group_reference );
			
			if( $row->biogrid_id != "0" && $row->biogrid_id != "-" ) {
				$links["biogrid"] = models\LinkoutGenerator::getLinkout( "biogrid", $row->biogrid_id );
			}
			
			$annotation = $this->twig->render( "view" . DS . "ViewGroupAnnotation.tpl", array(
				"ANNOTATION" => $annotationParams,
				"LINKS" => $links
			));
			
			return $annotation;
		}
		
		return false;
		
	}
	
	/**
	 * Check whether a user can access this view
	 */
	 
	public function canAccess( $viewID ) {
		
		// Public views are accessible to everyone
		$viewInfo = $this->fetchView( $viewID );
		if( $viewInfo->view_permission == "public" ) {
			return true;
		}
		
		// Users can access their own created views
		if( $viewInfo->user_id == $_SESSION[SESSION_NAME]['ID'] ) {
			return true;
		}
		
		// If the user is a member of an associated group
		$groups = json_decode( $viewInfo->view_groups, true ); 
		$userGroups = array_keys( $_SESSION[SESSION_NAME]['GROUPS'] );
		
		foreach( $groups as $groupID ) {
			if( in_array( $groupID, $userGroups )) {
				return true;
			}
		}
		
		// Otherwise, they cannot access it
		return false;
		
	}
	
	/**
	 * Format a Permission Entry for Display
	 */
	 
	private function formatPermission( $viewInfo ) {
		
		$permission = "";
		if( $viewInfo->view_permission == "public" ) {
			$permission = "<strong><span data-viewid='" . $viewInfo->view_id . "' class='text-success viewPermissionPopup optionIcon'>" . $viewInfo->view_permission . " <i class='fa fa-unlock'></i></span></strong>";
		} else {
			$permission = "<strong><span data-viewid='" . $viewInfo->view_id . "' class='text-danger viewPermissionPopup optionIcon'>" . $viewInfo->view_permission . " <i class='fa fa-lock'></i></span></strong>";
		}
		
		return $permission;
		
	}
	
	/**
	 * Fetch a nicely formatted set of privacy information
	 * based on a single view ID
	 */
	 
	public function fetchFormattedViewPrivacyDetails( $viewID ) {
		
		$userHandler = new models\UserHandler( );
		$groupHandler = new models\GroupHandler( );
		$groupList = $groupHandler->fetchGroups( );
		
		$view = "blocks" . DS . "ORCAPrivacyPopup.tpl";
		$viewInfo = $this->fetchView( $viewID );
		
		$userInfo = $userHandler->fetchUser( $viewInfo->user_id );
		$owner = $userInfo->user_firstname . " " . $userInfo->user_lastname;
		
		if( $viewInfo->view_permission == "public" ) {
			return $this->twig->render( $view, array( 
				"OWNER" => $owner
			));
		} else if( $viewInfo->view_permission == "private" ) {
			$groups = json_decode( $viewInfo->view_groups, true );
			$groupSet = array( );
			
			foreach( $groups as $groupID ) {
				$groupInfo = $groupList[$groupID];
				$groupSet[] = $groupInfo->group_name;
			}
			
			return $this->twig->render( $view, array( 
				"OWNER" => $owner,
				"GROUPS" => implode( ", ", $groupSet )
			));
		}
		
	}
	
	/**
	 * Change the permissions of a single view
	 */
	 
	public function changePermission( $viewID, $viewPermission, $viewGroups ) {
		
		$groupVal = array( );
		if( sizeof( $viewGroups ) > 0 ) {
			$groupVal = $viewGroups;
		}
		
		$groupVal = json_encode( $groupVal );
		
		$stmt = $this->db->prepare( "UPDATE " . DB_MAIN . ".views SET view_permission=?, view_groups=? WHERE view_id=?" );
		$stmt->execute( array( $viewPermission, $groupVal, $viewID ));
		
		return true;
		
	}
	
}

?>