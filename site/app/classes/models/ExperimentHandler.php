<?php

namespace ORCA\app\classes\models;

/**
 * Experiment Handler
 * This class is for handling processing of data
 * for experiments and related tables.
 */

use \PDO;
use ORCA\app\classes\models;
use ORCA\app\lib;
 
class ExperimentHandler {

	private $db;
	private $files;
	private $twig;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$this->files = new models\FileHandler( );
		
		$loader = new \Twig_Loader_Filesystem( TEMPLATE_PATH );
		$this->twig = new \Twig_Environment( $loader );
	}
	
	/**
	 * Fetch information about an experiment based on the passed in
	 * experiment ID, return false if non-existant
	 */
	 
	public function fetchExperiment( $expID ) {
		
		$stmt = $this->db->prepare( "SELECT * FROM " . DB_MAIN . ".experiments WHERE experiment_id=? LIMIT 1" );
		$stmt->execute( array( $expID ) );
		
		if( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			return $row;
		} 
		
		return false;
		
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
			$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".experiments VALUES( '0', ?, ?, ?, ?, ?, NOW( ), ?, 'inprogress', 'active', ? )" );
			$stmt->execute( array( $data->experimentName, $data->experimentDesc, $data->experimentCode, $data->experimentCell, $data->experimentDate, sizeof( $data->experimentFiles ), $_SESSION[SESSION_NAME]['ID'] ) );
			
			// Fetch its new ID
			$experimentID = $this->db->lastInsertId( );
			
			// Enter the list of files
			foreach( $data->experimentFiles as $file ) {
				$isBG = false;
				if( in_array( $file, $data->experimentBG ) ) {
					$isBG = true;
				}
				
				$this->files->addFile( $experimentID, $data->experimentCode, $file, $isBG );
			}
			
			$this->db->commit( );
			
			$this->files->removeStagingDir( $data->experimentCode );
			return array( "STATUS" => "success", "MESSAGE" => "Successfully Added Experiment", "ID" => $experimentID );
			
		} catch( PDOException $e ) {
			$this->db->rollback( );
			return array( "STATUS" => "error", "MESSAGE" => "Database Insert Problem. " . $e->getMessage( ) );
		}
		
	}
	
	/**
	 * Fetch column headers for an Experiment listing DataTable
	 */
	 
	 public function fetchExperimentColumnDefinitions( ) {
	 
		$columns = array( );
		$columns[0] = array( "title" => "", "data" => 0, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
		$columns[1] = array( "title" => "Name", "data" => 1, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'experiment_name' );
		$columns[2] = array( "title" => "Desc", "data" => 2, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'experiment_desc' );
		$columns[3] = array( "title" => "Cell Line", "data" => 3, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'cell_line_name' );
		$columns[4] = array( "title" => "Run Date", "data" => 4, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'experiment_rundate' );
		$columns[5] = array( "title" => "Files", "data" => 5, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'experiment_filecount' );
		$columns[6] = array( "title" => "File State", "data" => 6, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'experiment_filestate' );
		$columns[7] = array( "title" => "User", "data" => 7, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'user_name' );
		
		return $columns;
		
	}
	
	/**
	 * Fetch experiment results formatted correctly as rows for DataTable display
	 */
	 
	 public function buildExperimentRows( $params ) {
		
		$expList = $this->buildCustomizedExperimentList( $params );
		$rows = array( );
		foreach( $expList as $expID => $expInfo ) {
			$column = array( );
			$column[] = "<input type='checkbox' class='orcaDataTableRowCheck' value='" . $expID . "' />";
			$column[] = $expInfo->experiment_name;
			$column[] = $expInfo->experiment_desc;
			$column[] = $expInfo->cell_line_name;
			$column[] = $expInfo->experiment_rundate;
			$column[] = $expInfo->experiment_filecount;
			$column[] = $expInfo->experiment_filestate;
			$column[] = $expInfo->user_name;
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/**
	 * Build a base query with search params
	 * for DataTable construction
	 */
	 
	private function buildExperimentDataTableQuery( $params, $countOnly = false ) {
		
		$query = "SELECT ";
		if( $countOnly ) {
			$query .= " count(*) as rowCount";
		} else {
			$query .= " exp.*, cl.cell_line_id, cl.cell_line_name, u.user_name, u.user_firstname, u.user_lastname";
		}
		
		$query .= " FROM " . DB_MAIN . ".experiments exp LEFT JOIN cell_lines cl ON (exp.experiment_cellline = cl.cell_line_id) LEFT JOIN users u ON (exp.user_id = u.user_id)";
		
		$options = array( );
		$query .= " WHERE experiment_status='active'";
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " AND (experiment_name LIKE ? OR experiment_desc LIKE ? OR cell_line_name LIKE ? OR experiment_filecount=? OR experiment_filestate=? OR experiment_rundate=? OR user_name LIKE ?)";
			array_push( $options, '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', $params['search']['value'], $params['search']['value'], $params['search']['value'], '%' . $params['search']['value'] . '%' );
		}
		
		return array( "QUERY" => $query, "OPTIONS" => $options );
			
	}
	
	/**
	 * Build a set of experiment data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function buildCustomizedExperimentList( $params ) {
		
		$columnSet = $this->fetchExperimentColumnDefinitions( );
		
		$experiments = array( );
		
		$queryInfo = $this->buildExperimentDataTableQuery( $params, false );
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
			$experiments[$row->experiment_id] = $row;
		}
		
		return $experiments;
		
	}
	
	/**
	 * Build a count of experiment data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function getUnfilteredExperimentCount( $params ) {
		
		$queryInfo = $this->buildExperimentDataTableQuery( $params, true );
		$query = $queryInfo['QUERY'];
		$options = $queryInfo['OPTIONS'];
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->rowCount;
		
	}
	
	/**
	 * Get a count of all experiments available
	 */
	 
	public function fetchExperimentCount( ) {
		
		$stmt = $this->db->prepare( "SELECT COUNT(*) as expCount FROM " . DB_MAIN . ".experiments" );
		$stmt->execute( );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->expCount;
		
	}
	
	/**
	 * Fetch a set of buttons for the experiment view
	 * table toolbar
	 */
	
	public function fetchExperimentToolbar( ) {
		
		$buttons = array( );
		
		if( lib\Session::validateCredentials( lib\Session::getPermission( 'VIEW FILES' )) ) {
			$view = "blocks" . DS . "ORCADataTableToolbarButton.tpl";
			$buttons[] = $this->twig->render( $view, array( 
				"BTN_CLASS" => "btn-info experimentViewFilesBtn",
				"BTN_LINK" => "",
				"BTN_ID" => "experimentViewFilesBtn",
				"BTN_ICON" => "fa-file-text",
				"BTN_TEXT" => "View Files"
			));
		}
		
		if( lib\Session::validateCredentials( lib\Session::getPermission( 'MANAGE EXPERIMENTS' )) ) {
			$view = "blocks" . DS . "ORCADataTableToolbarDropdown.tpl";
			$buttons[] = $this->twig->render( $view, array(
				"BTN_CLASS" => "btn-danger",
				"BTN_ICON" => "fa-cog",
				"BTN_TEXT" => "Tools",
				"LINKS" => array(
					"experimentDisableChecked" => array( "linkHREF" => "", "linkText" => "Disable Checked Experiments", "linkClass" => "experimentDisableChecked" )
				)
			));
		}
		
		return implode( "", $buttons );
		
	}
	
	/**
	 * Disable experiments specified by ID passed in as an array
	 */
	
	public function disableExperiments( $expIDs ) {
		
		$querySet = array_fill( 0, sizeof( $expIDs ), "?" );
		$stmt = $this->db->prepare( "UPDATE " . DB_MAIN . ".experiments SET experiment_status='inactive' WHERE experiment_id IN (" . implode( ",", $querySet ) . ")" );
		$stmt->execute( $expIDs );
		
	}
}

?>