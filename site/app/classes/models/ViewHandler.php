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
	 
	 public function fetchViewColumnDefinitions( ) {
	 
		$columns = array( );
		$columns[0] = array( "title" => "", "data" => 0, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
		$columns[1] = array( "title" => "Name", "data" => 1, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'view_title' );
		$columns[2] = array( "title" => "Desc", "data" => 2, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'view_desc' );
		$columns[3] = array( "title" => "Type", "data" => 3, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'view_type_name' );
		$columns[4] = array( "title" => "Values", "data" => 4, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'view_value_name' );
		$columns[5] = array( "title" => "Run Date", "data" => 5, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'view_addeddate' );
		$columns[6] = array( "title" => "Files", "data" => 6, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'view_files' );
		$columns[7] = array( "title" => "State", "data" => 7, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'view_state' );
		$columns[8] = array( "title" => "User", "data" => 8, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'user_name' );
		
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
			$column[] = $viewInfo->view_type_name;
			$column[] = $viewInfo->view_value_name;
			$column[] = $viewInfo->view_addeddate;
			
			$fileSet = json_decode( $viewInfo->view_files );
			$fileList = array( );
			foreach( $fileSet as $fileID => $backgroundID ) {
				$backgroundSplit = explode( "|", $backgroundID );
				$fileList[] = $fileID;
				foreach( $backgroundSplit as $bgID ) {
					$fileList[] = $bgID;
				}
			}
			
			$fileList = array_unique( $fileList );
			$column[] = "[<a href='" . WEB_URL . "/Files?fileIDs=" . implode( "|", $fileList ) . "' title='View " . sizeof( $fileList ) . " Files'>View " . sizeof( $fileList ) . " Files</a>]";
			
			if( $viewInfo->view_state == "complete" ) {
				$column[] = "<strong><span class='text-success'>" . $viewInfo->view_state . " <i class='fa fa-check'></i></span></strong>";
			} else {
				$column[] = "<strong><span class='text-danger'>" . $viewInfo->view_state . " <i class='fa fa-spin fa-spinner'></i></a></span></strong>";
			} 
			
			$column[] = $viewInfo->user_name;
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/**
	 * Build a base query with search params
	 * for DataTable construction
	 */
	 
	private function buildViewDataTableQuery( $params, $countOnly = false ) {
		
		$query = "SELECT ";
		if( $countOnly ) {
			$query .= " count(*) as rowCount";
		} else {
			$query .= " view.*, vt.view_type_name, vv.view_value_name, u.user_name, u.user_firstname, u.user_lastname";
		}
		
		$query .= " FROM " . DB_MAIN . ".views view LEFT JOIN view_types vt ON (view.view_type_id = vt.view_type_id) LEFT JOIN view_values vv ON (view.view_value_id = vv.view_value_id) LEFT JOIN users u ON (view.user_id = u.user_id)";
		
		$options = array( );
		$query .= " WHERE view_status='active'";
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " AND (view_title LIKE ? OR view_desc LIKE ? OR view_type_name LIKE ? OR view_value_name LIKE ? OR view_state=? OR view_addeddate=? OR user_name LIKE ?)";
			array_push( $options, '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', $params['search']['value'], $params['search']['value'], '%' . $params['search']['value'] . '%' );
		}
		
		return array( "QUERY" => $query, "OPTIONS" => $options );
			
	}
	
	/**
	 * Build a set of view data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function buildCustomizedViewList( $params ) {
		
		$columnSet = $this->fetchViewColumnDefinitions( );
		
		$views = array( );
		
		$queryInfo = $this->buildViewDataTableQuery( $params, false );
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
			$views[$row->view_id] = $row;
		}
		
		return $views;
		
	}
	
	/**
	 * Build a count of view data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function getUnfilteredViewCount( $params ) {
		
		$queryInfo = $this->buildViewDataTableQuery( $params, true );
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
		
		$stmt = $this->db->prepare( "SELECT COUNT(*) as viewCount FROM " . DB_MAIN . ".views" );
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
		
		if( lib\Session::validateCredentials( lib\Session::getPermission( 'MANAGE VIEWS' )) ) {
			$view = "blocks" . DS . "ORCADataTableToolbarDropdown.tpl";
			$buttons[] = $this->twig->render( $view, array(
				"BTN_CLASS" => "btn-danger",
				"BTN_ICON" => "fa-cog",
				"BTN_TEXT" => "Tools",
				"LINKS" => array(
					"viewDisableChecked" => array( "linkHREF" => "", "linkText" => "Disable Checked Views", "linkClass" => "viewDisableChecked" )
				)
			));
		}
		
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
	
	
}

?>