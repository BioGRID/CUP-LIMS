<?php

namespace ORCA\app\classes\models;

/**
 * File Handler
 * This class is for handling processing of data
 * for files and related tables.
 */

use \PDO;
use ORCA\app\lib;
 
class FileHandler {

	private $db;
	private $twig;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$loader = new \Twig_Loader_Filesystem( TEMPLATE_PATH );
		$this->twig = new \Twig_Environment( $loader );
	}
	
	/** 
	 * Insert a file set into the database if one with the same
	 * name doesn't already exist.
	 */
	 
	public function insertFileSet( $data ) {
		
		// Otherwise, begin insert process
		$this->db->beginTransaction( );
		
		try {
			
			$fileIDs = array( );
			// Enter the list of files
			foreach( $data->files as $file ) {
				$isBG = false;
				if( in_array( $file, $data->fileBG ) ) {
					$isBG = true;
				}
				
				$fileID = $this->addFile( $data, $data->fileCode, $file, $isBG );
				if( $fileID ) {
					$fileIDs[] = $fileID;
				}
			}
			
			$this->db->commit( );
			
			$this->removeStagingDir( $data->fileCode );
			return array( "STATUS" => "success", "MESSAGE" => "Successfully Added Experiment", "IDS" => implode( "|", $fileIDs ) );
			
		} catch( PDOException $e ) {
			$this->db->rollback( );
			return array( "STATUS" => "error", "MESSAGE" => "Database Insert Problem. " . $e->getMessage( ) );
		}
		
	}
	
	/**
	 * Convert a set of files into a formatted alert indicating the current
	 * state of the file
	 */
	 
	public function fetchFormattedFileStates( $files ) {
		
		$formattedFiles = array( "INPROGRESS" => array( ), "COMPLETED" => array( ), "QUEUED" => array( ) );
		$fileCount = 0;
		$fileTotal = sizeof( $files );
		
		$stats = array( "TOTAL" => $fileTotal, "INPROGRESS" => 0, "SUCCESS" => 0, "ERROR" => 0, "QUEUED" => 0 );
		
		foreach( $files as $file ) {
			
			$fileCount++;

			$icon = "fa-check";
			$type = "success";
			$group = "QUEUED";
			$preamble = "";
			if( $file['STATE'] == "inprogress" ) {
				$icon = "fa-spinner fa-spin";
				$type = "warning";
				$preamble = "Processing File";
				$group = "INPROGRESS";
				$stats['INPROGRESS']++;
			} else if( $file['STATE'] == "parsed" ) {
				$icon = "fa-check";
				$type = "success";
				$preamble = "Successfully Processed";
				$group = "COMPLETED";
				$stats['SUCCESS']++;
			} else if( $file['STATE'] == "error" ) {
				$icon = "fa-warning";
				$type = "danger";
				$preamble = "Error Processing";
				$group = "COMPLETED";
				$stats['ERROR']++;
			} else if( $file['STATE'] == "new" || $file['STATE'] == "redo" ) {
				$icon = "fa-hourglass-start";
				$type = "info";
				$preamble = "Queued for Processing";
				$group = "QUEUED";
				$stats['QUEUED']++;
			}
			
			$params = array( 
				"FILE_NAME" => $file['NAME'],
				"FILE_SIZE" => $file['SIZE'],
				"ERRORS" => $file['STATE_MSG'],
				"PROCESS_PREAMBLE" => $preamble,
				"FILE_NUMBER" => $fileCount,
				"FILE_TOTAL" => $fileTotal,
				"ICON" => $icon,
				"TYPE" => $type
			);
			
			$view = "fileProgress" . DS . "FileProgressAlert.tpl";
			$formattedFiles[$group][] = $this->twig->render( $view, $params );
			
		}
		
		return array( "FILES" => $formattedFiles, "STATS" => $stats );
		
	}
	
	/** 
	 * Fetch a file from the database pertaining to a specific
	 * file ID
	 */
	 
	public function fetchFile( $fileID ) {
		
		$query = "SELECT f.file_id, f.file_name, f.file_size, f.file_state, f.file_state_msg, f.user_id, f.file_addeddate, f.file_readtotal, exp.experiment_id, exp.experiment_name, experiment_code FROM " . DB_MAIN . ".files f LEFT JOIN " . DB_MAIN . ".experiments exp ON (f.experiment_id=exp.experiment_id) WHERE file_id=?";

		$stmt = $this->db->prepare( $query );
		$stmt->execute( array( $fileID ) );
		
		// If it exists, return an error
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			return $row;
		}
		
		return false;
	
	}
	
	/** 
	 * Fetch a set of files from the database pertaining to a specific
	 * experiment ID and a status
	 */
	 
	public function fetchFiles( $expID ) {
		
		$query = "SELECT file_id, file_name, file_size, file_state, file_state_msg FROM " . DB_MAIN . ".files WHERE file_status='active' AND experiment_id=? ORDER BY file_addeddate DESC,file_id DESC";

		$stmt = $this->db->prepare( $query );
		$stmt->execute( array( $expID ) );
		
		$files = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$files[] = array( "NAME" => $row->file_name, "ID" => $row->file_id, "SIZE" => $this->formatFileSize( $row->file_size ), "STATE" => $row->file_state, "STATE_MSG" => json_decode( $row->file_state_msg, true ) );
		}
		
		return $files;
	
	}
	
	/** 
	 * Fetch a set of files from the database pertaining to a specific
	 * set of file IDs and a status
	 */
	 
	public function fetchFilesByIDs( $fileIDs ) {
		
		$varSet = array_fill( 0, sizeof( $fileIDs ), "?" );
		
		$query = "SELECT file_id, file_name, file_size, file_state, file_state_msg FROM " . DB_MAIN . ".files WHERE file_status='active' AND file_id IN (" . implode( ",", $varSet ) . ") ORDER BY file_addeddate DESC,file_id DESC";

		$stmt = $this->db->prepare( $query );
		$stmt->execute( $fileIDs );
		
		$files = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$files[] = array( "NAME" => $row->file_name, "ID" => $row->file_id, "SIZE" => $this->formatFileSize( $row->file_size ), "STATE" => $row->file_state, "STATE_MSG" => json_decode( $row->file_state_msg, true ) );
		}
		
		return $files;
	
	}
	
	/**
	 * Convert file sizes into a consise value
	 * that's easier to present
	 */
	 
	public function formatFileSize( $bytes ) {
		
        if( $bytes >= 1073741824 ) {
            $bytes = number_format( $bytes / 1073741824, 2 ) . ' GB';
        } else if( $bytes >= 1048576 ) {
            $bytes = number_format( $bytes / 1048576, 2 ) . ' MB';
        } else if( $bytes >= 1024 ) {
            $bytes = number_format( $bytes / 1024, 2 ) . ' kB';
        } else if( $bytes > 1 ) {
            $bytes = $bytes . ' bytes';
        } else if( $bytes == 1 ) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
		
	}
	
	/** 
	 * Insert a new file into the database if one with the same
	 * name doesn't already exist. Also move file into proper new
	 * home on the file system.
	 */
	 
	public function addFile( $fileSet, $fileCode, $filename, $isBG ) {
		
		$oldDir = UPLOAD_TMP_PATH . DS . $fileCode;
		$fileHash = sha1_file( $oldDir . DS . $filename );
		
		// See if one with the same name already exists
		$stmt = $this->db->prepare( "SELECT file_id FROM " . DB_MAIN . ".files WHERE file_hash=? LIMIT 1" );
		$stmt->execute( array( $fileHash ));
		
		// If it exists, return an error
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			return $row->file_id;
		}
		
		try {
			
			// Move File
			if( $fileInfo = $this->moveFileToProcessing( $filename, $fileCode )) {
				
				$bgVal = "0";
				if( $isBG ) {
					$bgVal = "1";
				}
				
				$groupVal = "-";
				if( sizeof( $fileSet->fileGroups ) > 0 ) {
					$groupVal = implode( "|", $fileSet->fileGroups );
				}
		
				// Create File
				$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".files VALUES( '0', ?, ?, ?, ?, ?, ?, ?, '0', NOW( ), ?, 'new','-', 'active', ?, ?, ? )" );
				$stmt->execute( array( $filename, $fileHash, $fileInfo['SIZE'], $fileCode, $fileSet->fileTags, $fileSet->fileDesc, $bgVal, $fileSet->fileDate, $_SESSION[SESSION_NAME]['ID'], $fileSet->filePermission, $groupVal ));
				
				// Fetch its new ID
				$fileID = $this->db->lastInsertId( );
				
				// return new file ID
				return $fileID;
				
			}
			
		} catch( Exception $e ) {
			echo $e->getMessage( );
		}
		
		return false;
		
	}
	
	/**
	 * Move a file from the staging area to the processed area
	 */
	 
	private function moveFileToProcessing( $filename, $expCode ) {
		
		try {
			
			$oldDir = UPLOAD_TMP_PATH . DS . $expCode;
			$newDir = UPLOAD_PROCESSED_PATH . DS . $expCode;
			
			// If directory doesn't exist, create it first
			if( !is_dir( $newDir )) {
				mkdir( $newDir, 0777, false );
			}
			
			// Test to see if file exists
			if( file_exists( $oldDir . DS . $filename )) {
				if( rename( $oldDir . DS . $filename, $newDir . DS . $filename )) {
					$fileSize = filesize( $newDir . DS . $filename );
					return array( "SIZE" => $fileSize, "PATH" => $newDir . DS . $filename );
				}
			}
				
		} catch( Exception $e ) {
			echo $e->getMessage( );
		}
		
		return false;
	}
	
	/**
	 * Clean out any remaining staging files that are not part
	 * of the final setup, then get rid of the directory
	 */
	 
	public function removeStagingDir( $expCode ) {
	 
		$dir = UPLOAD_TMP_PATH . DS . $expCode;
		
		// Remove any existing files, in case mistake uploads
		// occurred previously
		array_map( 'unlink', glob( $dir . DS . "*" ));
		
		// Remove Directory
		rmdir( $dir );
	
	}
	
	/**
	 * Fetch column headers for an experiment files listing DataTable
	 */
	 
	 public function fetchFilesViewColumnDefinitions( $showBGSelect = false ) {
	 
		$columns = array( );
		$columns[0] = array( "title" => "", "data" => 0, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
		$columns[1] = array( "title" => "Name", "data" => 1, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'file_name' );
		$columns[2] = array( "title" => "Size", "data" => 2, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_size' );
		$columns[3] = array( "title" => "Total Reads", "data" => 3, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_readtotal' );
		$columns[4] = array( "title" => "Date", "data" => 4, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_addeddate' );
		$columns[5] = array( "title" => "State", "data" => 5, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_state' );
		$columns[6] = array( "title" => "Options", "data" => 6, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
		
		if( $showBGSelect ) {
			$columns[7] = array( "title" => "Control", "data" => 7, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
		}
		
		return $columns;
		
	}
	
	/**
	 * Fetch list of backgrounds broken down by experiment ids
	 */
	 
	public function buildBGList( $params, $separated = false ) {
		
		$isExp = true;
		if( isset( $params['type'] ) && $params['type'] == "file" ) {
			$isExp = false;
		}
		
		$query = "SELECT file_id, file_name, experiment_id FROM " . DB_MAIN . ".files WHERE file_status='active' AND file_iscontrol='1'";
		
		$idSet = array( );
		if( isset( $params['ids'] )) {
			$idSet = explode( "|", $params['ids'] );
			if( sizeof( $idSet ) > 0 ) {
				if( $isExp ) {
					$options = $idSet;
					$varSet = array_fill( 0, sizeof( $idSet ), "?" );
					$query .= " AND experiment_id IN (" . implode( ",", $varSet ) . ")";
				} else {
					$options = $idSet;
					$varSet = array_fill( 0, sizeof( $idSet ), "?" );
					$query .= " AND file_id IN (" . implode( ",", $varSet ) . ")";
				}
			}
		}
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $idSet );
		
		$bgList = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
			
			if( $separated ) {
				if( !isset( $bgList[$row->experiment_id] )) {
					$bgList[$row->experiment_id] = array( );
				}
			
				$bgList[$row->experiment_id][] = $row;
				
			} else {
				$bgList[0][] = $row;
			}
		}
		
		return $bgList;
		
	}
	
	/**
	 * Fetch files results formatted correctly as rows for DataTable display
	 */
	 
	 public function buildFileRows( $params ) {
		
		$fileList = $this->buildCustomizedFileList( $params );
		$bgList = array( );
		if( isset( $params['showBGSelect'] ) && $params['showBGSelect'] == "true" ) {
			$bgList = $this->buildBGList( $params, true );
		}
		
		$rows = array( );
		foreach( $fileList as $fileID => $fileInfo ) {
			$column = array( );
			
			$checkedBoxes = array( );
			if( isset( $params['checkedBoxes'] )) {
				$checkedBoxes = $params['checkedBoxes'];
			}
			
			if( $fileInfo->file_state == "parsed" ) {
				if( isset( $checkedBoxes[$fileID] ) && $checkedBoxes[$fileID] ) {
					$column[] = "<input type='checkbox' class='orcaDataTableRowCheck' value='" . $fileID . "' checked />";
				} else {
					$column[] = "<input type='checkbox' class='orcaDataTableRowCheck' value='" . $fileID . "' />";
				}
			} else {
				$column[] = "";
			}
			
			$formattedName = "<a href='" . WEB_URL . "/Files/View?id=" . $fileInfo->file_id . "' title='" . $fileInfo->file_name . "'>" . $fileInfo->file_name . "</a>";
			if( $fileInfo->file_iscontrol == '1' ) {
				$formattedName .= " [control]";
			}
			$column[] = $formattedName;
			
			$column[] = $this->formatBytes( $fileInfo->file_size );
			$column[] = number_format( $fileInfo->file_readtotal, 0, ".", "," );
			$column[] = $fileInfo->file_addeddate;
			
			if( $fileInfo->file_state == "parsed" ) {
				$column[] = "<strong><span class='text-success'>" . $fileInfo->file_state . " <i class='fa fa-check'></i></span></strong>";
			} else {
				$column[] = "<strong><span class='text-danger'>" . $fileInfo->file_state . " <i class='fa fa-warning'></i></span></strong>";
			}
			
			$column[] = "<a href='" . WEB_URL . "/Experiment/View?id=" . $fileInfo->experiment_id . "' title='" . $fileInfo->experiment_name . "'>" . $fileInfo->experiment_name . "</a>";
			$column[] = $this->buildFilesTableOptions( $fileInfo );
			
			if( isset( $params['showBGSelect'] ) && $params['showBGSelect'] == "true" ) {
				$column[] = $this->generateBGSelect( $bgList, $fileInfo->experiment_id, "", "", false, false );
			}
			
			if( $fileInfo->file_state != "parsed" ) {
				$column['DT_RowClass'] = "orcaUnparsedFile";
			}
			
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/**
	 * Build a select list of backgrounds based on the passed in list
	 */
	 
	private function generateBGSelect( $bgList, $expID, $selectClass = "", $selectLabel = "", $skipAll = false, $forToolbar = false ) {
		$selectOptions = array( );
		
		if( isset( $bgList[$expID] )) {
			
			$allList = array( );
			$nameTest = array( );
			foreach( $bgList[$expID] as $bgInfo ) {
				
				$addOption = true;
				if( $forToolbar ) {
					if( isset( $nameTest[$bgInfo->file_name] ) ) {
						$addOption = false;
					} else {
						$nameTest[$bgInfo->file_name] = "";
					}
				}
				
				if( $addOption ) {
					$selectOptions[$bgInfo->file_id] = array( "SELECTED" => "", "NAME" => $bgInfo->file_name );
				}
				$allList[] = $bgInfo->file_id;
			}
			
			if( !$skipAll && sizeof( $bgList[$expID] ) > 1 ) {
				$selectOptions[implode( "|", $allList )] = array( "SELECTED" => "selected", "NAME" => "All Control Files" );;
			}
			
		} else {
			$selectOptions["0"] = array( "SELECTED" => "", "NAME" => "No Control Files" );
		}
		
		$view = "blocks" . DS . "ORCASelect.tpl";
		if( $forToolbar ) {
			$view = "blocks" . DS . "ORCADataTableToolbarSelect.tpl";
		}
		
		$select = $this->twig->render( $view, array(
			"OPTIONS" => $selectOptions,
			"SELECT_CLASS" => $selectClass,
			"SELECT_LABEL" => $selectLabel
		));
		
		return $select;
		
	}
	
	/**
	 * Build a base query with search params
	 * for DataTable construction
	 */
	 
	private function buildFilesDataTableQuery( $params, $countOnly = false ) {
		
		$includeBG = false;
		if( isset( $params['includeBG'] ) && $params['includeBG'] == true ) {
			$includeBG = true;
		}
		
		$isExp = true;
		if( isset( $params['type'] ) && $params['type'] == "file" ) {
			$isExp = false;
		}
		
		$query = "SELECT ";
		if( $countOnly ) {
			$query .= " count(*) as rowCount";
		} else {
			$query .= " f.*, exp.experiment_name, exp.experiment_code";
		}
		
		$query .= " FROM " . DB_MAIN . ".files f LEFT JOIN experiments exp ON (f.experiment_id=exp.experiment_id)";
		
		# Only add in an experiment ID filter if we
		# are passing in a specific set of experiments
		# to limit the listing to, otherwise return 
		# every file
		$options = array( );
		$query .= " WHERE file_status='active'";
		
		if( !$includeBG ) {
			$query .= " AND file_iscontrol='0'";
		}
		
		if( isset( $params['ids'] )) {
			$idSet = explode( "|", $params['ids'] );
			if( sizeof( $idSet ) > 0 ) {
				if( $isExp ) {
					$options = $idSet;
					$varSet = array_fill( 0, sizeof( $idSet ), "?" );
					$query .= " AND f.experiment_id IN (" . implode( ",", $varSet ) . ")";
				} else {
					$options = $idSet;
					$varSet = array_fill( 0, sizeof( $idSet ), "?" );
					$query .= " AND f.file_id IN (" . implode( ",", $varSet ) . ")";
				}
			}
		}
		
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " AND (file_name LIKE ? OR file_size=? OR file_readtotal=? OR file_addeddate LIKE ? OR file_state=? OR experiment_name LIKE ?)";
			array_push( $options, '%' . $params['search']['value'] . '%', $params['search']['value'], $params['search']['value'], '%' . $params['search']['value'] . '%', $params['search']['value'], '%' . $params['search']['value'] . '%' );
		}
		
		// echo $query;
		// print_r( $options );
		
		return array( "QUERY" => $query, "OPTIONS" => $options );
			
	}
	
	/**
	 * Build a set of file data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function buildCustomizedFileList( $params ) {
		
		$columnSet = $this->fetchFilesViewColumnDefinitions( );
		
		$files = array( );
		
		$queryInfo = $this->buildFilesDataTableQuery( $params, false );
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
			$files[$row->file_id] = $row;
		}
		
		return $files;
		
	}
	
	/**
	 * Build a count of file data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function getUnfilteredFileCount( $params ) {
		
		$queryInfo = $this->buildFilesDataTableQuery( $params, true );
		$query = $queryInfo['QUERY'];
		$options = $queryInfo['OPTIONS'];
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->rowCount;
		
	}
	
	/**
	 * Get a count of all files available
	 */
	 
	public function fetchFileCount( $ids, $includeBG = false, $isExp = true ) {
		
		$query = "SELECT COUNT(*) as fileCount FROM " . DB_MAIN . ".files WHERE file_status='active'";

		if( !$includeBG ) {
			$query .= " AND file_iscontrol='0'";
		}
		
		$options = array( );
		if( sizeof( $ids ) > 0 ) {
			if( $isExp ) {
				$options = $ids;
				$varSet = array_fill( 0, sizeof( $options ), "?" );
				$query .= " AND experiment_id IN (" . implode( ",", $varSet ) . ")";
			} else {
				$options = $ids;
				$varSet = array_fill( 0, sizeof( $options ), "?" );
				$query .= " AND file_id IN (" . implode( ",", $varSet ) . ")";
			}
		} 
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->fileCount;
		
	}
	
	/**
	 * Convert a file size represented in bytes to a 
	 * more human readable alternative
	 */
	
	public function formatBytes( $bytes, $precision = 2 ) { 
	
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' ); 

		$bytes = max( $bytes, 0 ); 
		$pow = floor(( $bytes ? log( $bytes ) : 0) / log( 1024 )); 
		$pow = min( $pow, count( $units ) - 1); 

		$bytes /= pow( 1024, $pow );

		return round( $bytes, $precision ) . ' ' . $units[$pow]; 
		
	}
	
	/**
	 * Fetch a toolbar with buttons only available when adding a view
	 */
	 
	public function fetchFileToolbarForAddView( $expIDs ) {
		$buttons = array( );
		
		$bgList = $this->buildBGList( array( "expIDs" => implode( "|", $expIDs )), false );
		$selectList = $this->generateBGSelect( $bgList, 0, "pull-right col-lg-2 col-md-3 col-sm-4 col-xs-6", "Control: ", false, true );
		$buttons[] = $selectList;
		
		return implode( "", $buttons );
	}
	
	/**
	 * Fetch the set of toolbar buttons for the raw file list view
	 */
	 
	public function fetchFileToolbar( ) {
		
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
		
		if( lib\Session::validateCredentials( lib\Session::getPermission( 'CREATE VIEW' )) ) {
			$view = "blocks" . DS . "ORCADataTableToolbarDropdown.tpl";
			$buttons[] = $this->twig->render( $view, array(
				"BTN_CLASS" => "btn-info",
				"BTN_ICON" => "fa-table",
				"BTN_TEXT" => "Matrix View",
				"LINKS" => array(
					"viewMatrixLog2" => array( "linkHREF" => "", "linkText" => "View Files in Log2 Matrix", "linkClass" => "viewClick", "linkData" => array( "type" => "1", "values" => "1" ))
				)
			));
		}
		
		return implode( "", $buttons );
		
	}
	
	/**
	 * Build out the options for the Files Table Field
	 */
	 
	private function buildFilesTableOptions( $fileInfo ) {
		
		$options = array( );

		if( lib\Session::validateCredentials( lib\Session::getPermission( 'DOWNLOAD FILES' )) ) {
			$options[] = '<a href="' . UPLOAD_PROCESSED_URL . "/" . $fileInfo->experiment_code . "/" . $fileInfo->file_name . '" title="' . $fileInfo->file_name . '" target="_BLANK"><i class="optionIcon fa fa-download fa-lg popoverData fileDownload text-info" data-title="Download Raw Data" data-content="Click to download this raw data file."></i></a>';
		}
		
		$options[] = '<a href="' . WEB_URL . "/Files/View?id=" . $fileInfo->file_id . '" title="' . $fileInfo->file_name . '"><i class="optionIcon fa fa-search-plus fa-lg popoverData fileView text-primary" data-title="View File Details" data-content="Click to view this raw data file in expanded details."></i></a>';

		
		return implode( " ", $options );
		
	}
	
}

?>