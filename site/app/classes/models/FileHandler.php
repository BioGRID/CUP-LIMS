<?php

namespace ORCA\app\classes\models;

/**
 * File Handler
 * This class is for handling processing of data
 * for files and related tables.
 */

use \PDO;
use ORCA\app\lib;
use ORCA\app\classes\models;
 
class FileHandler {

	private $db;
	private $twig;
	private $maxNameLength = 40;
	private $annotationFiles;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$loader = new \Twig_Loader_Filesystem( TEMPLATE_PATH );
		$this->twig = new \Twig_Environment( $loader );
		
		$this->annotationFiles = $this->fetchAnnotationFiles( );
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
			return array( "STATUS" => "success", "MESSAGE" => "Successfully Added Files", "IDS" => implode( "|", $fileIDs ) );
			
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
		
		$query = "SELECT file_id, file_name, file_size, file_state, file_state_msg, user_id, file_addeddate, file_readtotal, file_code, file_desc, file_tags, file_permission, file_groups FROM " . DB_MAIN . ".files WHERE file_id=?";

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
				
				$groupVal = array( );
				if( sizeof( $fileSet->fileGroups ) > 0 ) {
					$groupVal = $fileSet->fileGroups;
				}
				
				$groupVal = json_encode( $groupVal );
		
				// Create File
				$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".files VALUES( '0', ?, ?, ?, ?, ?, ?, ?, '0', ?, NOW( ), ?, 'new','-', 'active', ?, ?, ? )" );
				$stmt->execute( array( $filename, $fileHash, $fileInfo['SIZE'], $fileCode, $fileSet->fileTags, $fileSet->fileDesc, $bgVal, $fileSet->fileAnnotation, $fileSet->fileDate, $_SESSION[SESSION_NAME]['ID'], $fileSet->filePermission, $groupVal ));
				
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
	 * Change the permissions of a single file
	 */
	 
	public function changePermission( $fileID, $filePermission, $fileGroups ) {
		
		$groupVal = array( );
		if( sizeof( $fileGroups ) > 0 ) {
			$groupVal = $fileGroups;
		}
		
		$groupVal = json_encode( $groupVal );
		
		$stmt = $this->db->prepare( "UPDATE " . DB_MAIN . ".files SET file_permission=?, file_groups=? WHERE file_id=?" );
		$stmt->execute( array( $filePermission, $groupVal, $fileID ));
		
		return true;
		
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
		if( !$showBGSelect ) {
			
			$columns[0] = array( "title" => "", "data" => 0, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
			$columns[1] = array( "title" => "Name", "data" => 1, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'file_name' );
			$columns[2] = array( "title" => "Desc", "data" => 2, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'file_desc' );
			$columns[3] = array( "title" => "Tags", "data" => 3, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_tags' );
			$columns[4] = array( "title" => "Size", "data" => 4, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_size' );
			$columns[5] = array( "title" => "ReadSum", "data" => 5, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_readtotal' );
			$columns[6] = array( "title" => "Date", "data" => 6, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_addeddate' );
			$columns[7] = array( "title" => "State", "data" => 7, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_state' );
			$columns[8] = array( "title" => "Privacy", "data" => 8, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_permission' );
			$columns[9] = array( "title" => "Options", "data" => 9, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
			
		} else {
			
			$columns[0] = array( "title" => "", "data" => 0, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
			$columns[1] = array( "title" => "Name", "data" => 1, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'file_name' );
			$columns[2] = array( "title" => "Desc", "data" => 2, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'file_desc' );
			$columns[3] = array( "title" => "ReadSum", "data" => 3, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_readtotal' );
			$columns[4] = array( "title" => "Date", "data" => 4, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_addeddate' );
			$columns[5] = array( "title" => "State", "data" => 5, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_state' );
			$columns[6] = array( "title" => "Privacy", "data" => 6, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'file_permission' );
			$columns[7] = array( "title" => "Mapping", "data" => 7, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
			$columns[8] = array( "title" => "Control", "data" => 8, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
			
		}
		
		return $columns;
		
	}
	
	/**
	 * Fetch list of backgrounds broken down file code
	 */
	 
	public function buildBGList( $params, $separated = false ) {
		
		$codeList = array( );
		$query = "SELECT file_id, file_name, file_code FROM " . DB_MAIN . ".files WHERE file_status='active' AND file_iscontrol='1'";
		
		if( isset( $params['fileIDs'] ) && strlen( $params['fileIDs'] ) > 0 ) {
			
			$codeQuery = "SELECT file_code FROM " . DB_MAIN . ".files WHERE file_status='active'";
			$idSet = explode( "|", $params['fileIDs'] );
			$options = $idSet;
			$varSet = array_fill( 0, sizeof( $idSet ), "?" );
			$codeQuery .= " AND file_id IN (" . implode( ",", $varSet ) . ") GROUP BY file_code";
			
			$stmt = $this->db->prepare( $codeQuery );
			$stmt->execute( $idSet );
			
			$codeList = array( );
			while( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
				$codeList[] = $row->file_code;
			}
			
			$varSet = array_fill( 0, sizeof( $codeList ), "?" );
			$query .= " AND file_code IN (" . implode( ",", $varSet ) . ")";
			
		} 
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $codeList );
		
		$bgList = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ )) {
			
			if( $separated ) {
				if( !isset( $bgList[$row->file_code] )) {
					$bgList[$row->file_code] = array( );
				}
			
				$bgList[$row->file_code][] = $row;
				
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
			$column[] = $fileInfo->file_desc;
			
			if( !isset( $params['showBGSelect'] ) || $params['showBGSelect'] == "false" ) {
				$column[] = $fileInfo->file_tags;
				$column[] = $this->formatBytes( $fileInfo->file_size );
			}
			
			$column[] = number_format( $fileInfo->file_readtotal, 0, ".", "," );
			$column[] = $fileInfo->file_addeddate;
			
			$column[] = $this->formatState( $fileInfo );
			
			$column[] = $this->formatPermission( $fileInfo );
			
			if( isset( $params['showBGSelect'] ) && $params['showBGSelect'] == "true" ) {
				$column[] = $this->generateMappingSelect( $fileInfo->annotation_file_id, "mappingSelect", "", false );
				$column[] = $this->generateBGSelect( $bgList, $fileInfo->file_code, "controlFileSelect", "", false, false );
			} else {
				$column[] = $this->buildFilesTableOptions( $fileInfo );
			}
			
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/**
	 * Build a formatted list of selectable mapping files
	 */
	 
	private function generateMappingSelect( $selectedOption, $selectClass = "", $selectLabel = "", $forToolbar = false ) {
		
		$selectOptions = array( );
		
		foreach( $this->annotationFiles as $annotationFileID => $annotationFileInfo ) {
			if( $annotationFileID == $selectedOption ) {
				$selectOptions[$annotationFileID] = array( "SELECTED" => "selected", "NAME" => $annotationFileInfo->annotation_file_name );
			} else {
				$selectOptions[$annotationFileID] = array( "SELECTED" => "", "NAME" => $annotationFileInfo->annotation_file_name );
			}
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
	 * Format a State Entry for Display
	 */
	 
	private function formatState( $fileInfo ) {
		
		$state = "";
		if( $fileInfo->file_state == "parsed" ) {
			$state = "<strong><span class='text-success'>" . $fileInfo->file_state . " <i class='fa fa-check'></i></span></strong>";
		} else {
			$state = "<strong><span class='text-danger'>" . $fileInfo->file_state . " <i class='fa fa-warning'></i></span></strong>";
		}
		
		return $state;
		
	}
	
	/**
	 * Format a Permission Entry for Display
	 */
	 
	private function formatPermission( $fileInfo ) {
		
		$permission = "";
		if( $fileInfo->file_permission == "public" ) {
			$permission = "<strong><span data-fileid='" . $fileInfo->file_id . "' class='text-success filePermissionPopup optionIcon'>" . $fileInfo->file_permission . " <i class='fa fa-unlock'></i></span></strong>";
		} else {
			$permission = "<strong><span data-fileid='" . $fileInfo->file_id . "' class='text-danger filePermissionPopup optionIcon'>" . $fileInfo->file_permission . " <i class='fa fa-lock'></i></span></strong>";
		}
		
		return $permission;
		
	}
	
	/**
	 * Build a select list of backgrounds based on the passed in list
	 */
	 
	private function generateBGSelect( $bgList, $fileCode, $selectClass = "", $selectLabel = "", $skipAll = false, $forToolbar = false ) {
		$selectOptions = array( );
		
		if( isset( $bgList[$fileCode] )) {
			
			$allList = array( );
			$nameTest = array( );
			foreach( $bgList[$fileCode] as $bgInfo ) {
				
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
			
			if( !$skipAll && sizeof( $bgList[$fileCode] ) > 1 ) {
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
		
		$query = "SELECT ";
		if( $countOnly ) {
			$query .= " count(*) as rowCount";
		} else {
			$query .= " *";
		}
		
		$query .= " FROM " . DB_MAIN . ".files";
		
		# Only add in an experiment ID filter if we
		# are passing in a specific set of experiments
		# to limit the listing to, otherwise return 
		# every file
		$options = array( );
		$query .= " WHERE file_status='active'";
		
		if( !$includeBG ) {
			$query .= " AND file_iscontrol='0'";
		}
		
		if( isset( $params['ids'] ) && strlen( $params['ids'] ) > 0 ) {
			$idSet = explode( "|", $params['ids'] );
			$options = $idSet;
			$varSet = array_fill( 0, sizeof( $idSet ), "?" );
			$query .= " AND file_id IN (" . implode( ",", $varSet ) . ")";
		} 
		
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " AND (file_name LIKE ? OR file_size=? OR file_readtotal=? OR file_addeddate LIKE ? OR file_state=? OR file_desc LIKE ? OR file_tags LIKE ?)";
			array_push( $options, '%' . $params['search']['value'] . '%', $params['search']['value'], $params['search']['value'], '%' . $params['search']['value'] . '%', $params['search']['value'], '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%' );
		}
		
		// Addon Permission Check Query Params
		$query = $this->buildFilesPermissionQuery( $query );
		
		return array( "QUERY" => $query, "OPTIONS" => $options );
			
	}
	
	/**
	 * Return the original passed in query with attached permissions
	 * data
	 */
	 
	private function buildFilesPermissionQuery( $query, $prepend = "" ) {
		
		// Check for valid permissions to access
		if( $prepend != "" ) {
			$query .= " AND (" . $prepend . "user_id='" . $_SESSION[SESSION_NAME]['ID'] . "' OR " . $prepend . "file_permission='public'";
		} else {
			$query .= " AND (user_id='" . $_SESSION[SESSION_NAME]['ID'] . "' OR file_permission='public'";
		}
		
		// Add Group Check
		if( sizeof( $_SESSION[SESSION_NAME]['GROUPS'] ) > 0 ) {
			$groupIDs = array_keys( $_SESSION[SESSION_NAME]['GROUPS'] );
			if( $prepend != "" ) {
				$query .= " OR (" . $prepend . "file_groups LIKE '%\"" . implode( "\"%' OR " . $prepend . "file_groups LIKE '%\"", $groupIDs ) . "\"%'))";
			} else {
				$query .= " OR (file_groups LIKE '%\"" . implode( "\"%' OR file_groups LIKE '%\"", $groupIDs ) . "\"%'))";
			}
		} else {
			$query .= ")";
		}
		
		return $query;
		
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
	 
	public function fetchFileCount( $ids, $includeBG = false ) {
		
		$query = "SELECT COUNT(*) as fileCount FROM " . DB_MAIN . ".files WHERE file_status='active'";

		if( !$includeBG ) {
			$query .= " AND file_iscontrol='0'";
		}
		
		$options = array( );
		 
		if( sizeof( $ids ) > 0 ) {
			$options = $ids;
			$varSet = array_fill( 0, sizeof( $options ), "?" );
			$query .= " AND file_id IN (" . implode( ",", $varSet ) . ")";
		} 
		
		// Addon Permission Check Query Params
		$query = $this->buildFilesPermissionQuery( $query );
		
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
	 
	public function fetchFileToolbarForAddView( $fileIDs ) {
		$buttons = array( );
		
		$bgList = $this->buildBGList( array( "fileIDs" => implode( "|", $fileIDs )), false );
		$selectList = $this->generateBGSelect( $bgList, 0, "orcaToolbarControlSelect pull-right col-lg-2 col-md-3 col-sm-4 col-xs-6", "Control: ", false, true );
		$buttons[] = $selectList;
		
		$selectList = $this->generateMappingSelect( "", "orcaToolbarMappingSelect pull-right col-lg-2 col-md-3 col-sm-4 col-xs-6", "Mapping: ", true );
		$buttons[] = $selectList;
		
		return implode( "", $buttons );
	}
	
	/**
	 * Fetch the set of toolbar buttons for the raw file list view
	 */
	 
	public function fetchFileToolbar( ) {
		
		$buttons = array( );
		
		if( lib\Session::validateCredentials( lib\Session::getPermission( 'CREATE VIEW' )) ) {
			$view = "blocks" . DS . "ORCADataTableToolbarButton.tpl";
			$buttons[] = $this->twig->render( $view, array( 
				"BTN_CLASS" => "btn-orca2 fileCreateViewBtn",
				"BTN_LINK" => "",
				"BTN_ID" => "fileCreateViewBtn",
				"BTN_ICON" => "fa-bar-chart",
				"BTN_TEXT" => "Create View"
			));
		}
		
		return implode( "", $buttons );
		
	}
	
	/**
	 * Fetch a nicely formatted set of privacy information
	 * based on a single file ID
	 */
	 
	public function fetchFormattedFilePrivacyDetails( $fileID ) {
		
		$userHandler = new models\UserHandler( );
		$groupHandler = new models\GroupHandler( );
		$groupList = $groupHandler->fetchGroups( );
		
		$view = "blocks" . DS . "ORCAPrivacyPopup.tpl";
		$fileInfo = $this->fetchFile( $fileID );
		
		$userInfo = $userHandler->fetchUser( $fileInfo->user_id );
		$owner = $userInfo->user_firstname . " " . $userInfo->user_lastname;
		
		if( $fileInfo->file_permission == "public" ) {
			return $this->twig->render( $view, array( 
				"OWNER" => $owner
			));
		} else if( $fileInfo->file_permission == "private" ) {
			$groups = json_decode( $fileInfo->file_groups, true );
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
	 * Build out the options for the Files Table Field
	 */
	 
	private function buildFilesTableOptions( $fileInfo ) {
		
		$options = array( );

		if( lib\Session::validateCredentials( lib\Session::getPermission( 'DOWNLOAD FILES' )) ) {
			$options[] = '<a href="' . UPLOAD_PROCESSED_URL . "/" . $fileInfo->file_code . "/" . $fileInfo->file_name . '" title="' . $fileInfo->file_name . '" target="_BLANK"><i class="optionIcon fa fa-download fa-lg popoverData fileDownload text-info" data-title="Download Raw Data" data-content="Click to download this raw data file."></i></a>';
		}
		
		$options[] = '<a href="' . WEB_URL . "/Files/View?id=" . $fileInfo->file_id . '" title="' . $fileInfo->file_name . '"><i class="optionIcon fa fa-search-plus fa-lg popoverData fileView text-primary" data-title="View File Details" data-content="Click to view this raw data file in expanded details."></i></a>';

		
		return implode( " ", $options );
		
	}
	
	/**
	 * Check whether a user can access this file
	 */
	 
	public function canAccess( $fileID ) {
		
		// Public files are accessible to everyone
		$fileInfo = $this->fetchFile( $fileID );
		if( $fileInfo->file_permission == "public" ) {
			return true;
		}
		
		// Users can access their own created files
		if( $fileInfo->user_id == $_SESSION[SESSION_NAME]['ID'] ) {
			return true;
		}
		
		// If the user is a member of an associated group
		$groups = json_decode( $fileInfo->file_groups, true ); 
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
	 * Fetch a recent list of files limited by ID if not empty
	 */
	 
	public function fetchFileList( $userID = "", $limit = 5 ) {

		$options = array( );
		$query = "SELECT f.file_id, f.file_name, f.file_size, f.file_addeddate, f.file_code, DATE_FORMAT( f.file_addeddate, '%Y-%m-%d'  ) as addedDate, f.file_state, f.file_permission, u.user_name FROM " . DB_MAIN . ".files f LEFT JOIN " . DB_MAIN . ".users u ON (f.user_id=u.user_id) WHERE f.file_status='active' AND file_iscontrol='0'";
		
		if( $userID != "" ) {
			$options[] = $userID;
			$query .= " AND u.user_id=?";
		}
		
		$query = $this->buildFilesPermissionQuery( $query, "f." );
		
		$query .= " ORDER BY f.file_addeddate DESC LIMIT " . $limit;
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		$files = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			
			// Keep Name Small for Home Display
			$formattedName = $row->file_name;
			if( strlen( $formattedName ) > $this->maxNameLength ) {
				$formattedName = substr( $formattedName, 0, $this->maxNameLength ) . "...";
			}
			
			$files[$row->file_id] = array( 
				"ID" => $row->file_id,
				"NAME" => $formattedName,
				"ADDED_DATE" => $row->addedDate,
				"SIZE" => $this->formatBytes( $row->file_size ),
				"PERMISSION" => $this->formatPermission( $row ),
				"STATE" => $this->formatState( $row ),
				"USER_NAME" => $row->user_name,
				"OPTIONS" => $this->buildFilesTableOptions( $row )
			);
		}
		
		return $files;
		
	}
	
	/** 
	 * Insert a new annotation file into the database if one with the same
	 * hash doesn't already exist. Also move file into proper new
	 * home on the file system.
	 */
	 
	public function addAnnotationFile( $fileSet, $fileCode, $filename ) {
		
		$oldDir = UPLOAD_TMP_PATH . DS . $fileCode;
		$fileHash = sha1_file( $oldDir . DS . $filename );
		
		// See if one with the same hash already exists
		$stmt = $this->db->prepare( "SELECT annotation_file_id FROM " . DB_MAIN . ".annotation_files WHERE annotation_file_hash=? LIMIT 1" );
		$stmt->execute( array( $fileHash ));
		
		// If it exists, return an error
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			$this->removeStagingDir( $fileCode );
			return array( "STATUS" => "success", "MESSAGE" => "Successfully Added Files", "IDS" => $row->annotation_file_id );
		}
		
		try {
			
			// Move File
			if( $fileInfo = $this->moveFileToProcessing( $filename, $fileCode )) {
		
				// Create File
				$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".annotation_files VALUES( '0', ?, ?, ?, ?, ?, ?, NOW( ), 'new','-', 'active', ? )" );
				$stmt->execute( array( $filename, $fileHash, $fileInfo['SIZE'], $fileCode, $fileSet->annotationDesc, $fileSet->annotationOrganism, $_SESSION[SESSION_NAME]['ID'] ));
				
				// Fetch its new ID
				$fileID = $this->db->lastInsertId( );
				
				$this->removeStagingDir( $fileCode );
				return array( "STATUS" => "success", "MESSAGE" => "Successfully Added Files", "IDS" => $fileID );
				
			}
			
		} catch( Exception $e ) {
			return array( "STATUS" => "error", "MESSAGE" => "Database Insert Problem. " . $e->getMessage( ) );
		}
		
		return array( "STATUS" => "error", "MESSAGE" => "Unknown Error" );
		
	}
	
	/** 
	 * Fetch a file from the database pertaining to a specific
	 * annotation file ID
	 */
	 
	public function fetchAnnotationFile( $fileID ) {
		
		$query = "SELECT * FROM " . DB_MAIN . ".annotation_files WHERE annotation_file_id=?";

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
	 * Fetch a set of annotation files from the database pertaining to a specific
	 * set of annotation file IDs and a status
	 */
	 
	public function fetchAnnotationFilesByIDs( $fileIDs ) {
		
		$varSet = array_fill( 0, sizeof( $fileIDs ), "?" );
		
		$query = "SELECT * FROM " . DB_MAIN . ".annotation_files WHERE annotation_file_status='active' AND annotation_file_id IN (" . implode( ",", $varSet ) . ") ORDER BY annotation_file_addeddate DESC, annotation_file_id DESC";

		$stmt = $this->db->prepare( $query );
		$stmt->execute( $fileIDs );
		
		$files = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$files[] = array( "NAME" => $row->annotation_file_name, "ID" => $row->annotation_file_id, "SIZE" => $this->formatFileSize( $row->annotation_file_size ), "STATE" => $row->annotation_file_state, "STATE_MSG" => json_decode( $row->annotation_file_state_msg, true ) );
		}
		
		return $files;
	
	}
	
	/** 
	 * Fetch all annotation files from the database 
	 */
	 
	public function fetchAnnotationFiles( ) {
		
		$query = "SELECT * FROM " . DB_MAIN . ".annotation_files WHERE annotation_file_status='active' ORDER BY annotation_file_name ASC";

		$stmt = $this->db->prepare( $query );
		$stmt->execute( );
		
		$files = array( );
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$files[$row->annotation_file_id] = $row;
		}
		
		return $files;
	
	}
	
}

?>