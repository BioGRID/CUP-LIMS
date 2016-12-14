<?php

namespace ORCA\app\classes\models;

/**
 * File Handler
 * This class is for handling processing of data
 * for files and related tables.
 */

use \PDO;
 
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
	 * Convert a set of files into a formatted alert indicating the current
	 * state of the file
	 */
	 
	public function fetchFormattedFileStates( $files ) {
		
		$formattedFiles = array( );
		$fileCount = 0;
		$fileTotal = sizeof( $files );
		foreach( $files as $file ) {
			
			$fileCount++;

			$icon = "fa-check";
			$type = "success";
			$preamble = "";
			if( $file['STATE'] == "inprogress" ) {
				$icon = "fa-spinner fa-spin";
				$type = "warning";
				$preamble = "Processing File";
			} else if( $file['STATE'] == "parsed" ) {
				$icon = "fa-check";
				$type = "success";
				$preamble = "Successfully Processed";
			} else if( $file['STATE'] == "error" ) {
				$icon = "fa-warning";
				$type = "danger";
				$preamble = "Error Processing";
			} else if( $file['STATE'] == "new" || $file['STATE'] == "redo" ) {
				$icon = "fa-hourglass-start";
				$type = "info";
				$preamble = "Queued for Processing";
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
			$formattedFiles[] = $this->twig->render( $view, $params );
			
		}
		
		return $formattedFiles;
		
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
	 * Convert file sizes into a consise value
	 * that's easier to present
	 */
	 
	private function formatFileSize( $bytes ) {
		
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
	 
	public function addFile( $expID, $expCode, $filename ) {
		
		// See if one with the same name already exists
		$stmt = $this->db->prepare( "SELECT file_id FROM " . DB_MAIN . ".files WHERE file_name=? AND experiment_id=? LIMIT 1" );
		$stmt->execute( array( $filename, $expID ));
		
		// If it exists, return an error
		if( $stmt->rowCount( ) > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_OBJ );
			return $row->file_id;
		}
		
		try {
			
			// Move File
			if( $fileInfo = $this->moveFileToProcessing( $filename, $expCode )) {
		
				// Create File
				$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".files VALUES( '0', ?, ?, NOW( ), 'new','-', 'active', ?, ? )" );
				$stmt->execute( array( $filename, $fileInfo['SIZE'], $expID, $_SESSION[SESSION_NAME]['ID'] ));
				
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
	
}

?>