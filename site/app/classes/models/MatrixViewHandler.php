<?php

namespace ORCA\app\classes\models;

/**
 * Matrix View Handler
 * This class is for handling processing of data
 * for the creation of a matrix view
 */

use \PDO;
use ORCA\app\lib;
use ORCA\app\classes\models;
 
class MatrixViewHandler {

	private $db;
	private $twig;

	public function __construct( $viewID ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$loader = new \Twig_Loader_Filesystem( TEMPLATE_PATH );
		$this->twig = new \Twig_Environment( $loader );
		
		$this->viewHandler = new models\ViewHandler( );
		$this->view = $this->viewHandler->fetchView( $viewID );
		
		$this->min = 0;
		$this->max = 0;
		
	}
	
	/**
	 * Fetch column headers for a Matrix View listing DataTable
	 */
	 
	 public function fetchColumnDefinitions( ) {
		 
		$conditionCols = json_decode( $this->view->view_details, true );
		$this->min = $conditionCols['MIN'];
		$this->max = $conditionCols['MAX'];
		$conditionCols = $conditionCols['FILES'];
		ksort( $conditionCols, SORT_NATURAL );
		
		
	 
		$columns = array( );
		$columns[0] = array( "title" => "Name", "data" => 0, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'group_name' );
		
		$columnCount = 1;
		$columnNameCount = 0;
		foreach( $conditionCols as $conditionID => $conditionDetails ) {
			$columns[$columnCount] = array( "title" => "<a class='matrixHeaderPopup' data-fileid='" . $conditionDetails['FILE']['ID'] . "' data-file='" . $conditionDetails['FILE']['NAME'] . "' data-bgid='" . $conditionDetails['BG']['ID'] . "' data-bgfile='" . $conditionDetails['BG']['NAME'] . "'>" . $this->getExcelNameFromNumber( $columnNameCount ) . "</a>", "data" => $columnCount, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => $conditionID );
			$columnCount++;
			$columnNameCount++;
		}
		
		return $columns;
		
	}
	
	/**
	 * Get Excel like name for columns so we can keep column names
	 * more concise to save space
	 */
	 
	private function getExcelNameFromNumber($num) {
		$numeric = $num % 26;
		$letter = chr( 65 + $numeric );
		$num2 = intval( $num / 26 );
		if ($num2 > 0) {
			return $this->getExcelNameFromNumber($num2 - 1) . $letter;
		} else {
			return $letter;
		}
	}
	
	/**
	 * Fetch view results formatted correctly as rows for DataTable display
	 */
	 
	 public function buildRows( $params ) {
		
		$rowList = $this->buildCustomizedRowList( $params );
		$rows = array( );
		foreach( $rowList as $rowID => $rowInfo ) {
			$column = array( );
			
			$style = 1;
			if( isset( $params['viewStyle'] ) ) {
				if( $params['viewStyle'] == 2 ) {
					$style = 2;
				} else if( $params['viewStyle'] == 3 ) {
					$style = 3;
				}
			} 
			
			// $checkedBoxes = array( );
			// if( isset( $params['checkedBoxes'] )) {
				// $checkedBoxes = $params['checkedBoxes'];
			// }
			
			// if( isset( $checkedBoxes[$rowID] ) && $checkedBoxes[$rowID] ) {
				// $column[] = "<input type='checkbox' class='orcaDataTableRowCheck' value='" . $rowID . "' checked />";
			// } else {
				// $column[] = "<input type='checkbox' class='orcaDataTableRowCheck' value='" . $rowID . "' />";
			// }
			
			if( $rowInfo->sgrna_group_reference_type == "BIOGRID" ) {
				$column[] = "<a class='annotationPopup' data-id='" . $rowInfo->sgrna_group_id . "' data-type='BIOGRID'>" . $rowInfo->group_name . "</a>";
			} else {
				$column[] = $rowInfo->group_name;
			}

			$columnSet = $this->fetchColumnDefinitions(  );
			for( $i = 1; $i < sizeof( $columnSet ); $i++ ) {
				$colName = $columnSet[$i]["dbCol"];
				if( $style == 2 ) {
					$column[] = round( $rowInfo->$colName, 5 );
				} else if( $style == 3 ) {
					$column[] = "<div style='background-color: " . $this->convertValueToRGB( $rowInfo->$colName ) . "; color: #FFF; width: 100%; height: 100%;'>" . round( $rowInfo->$colName, 5 ) . "</div>";
				} else {
					$column[] = "<div style='background-color: " . $this->convertValueToRGB( $rowInfo->$colName ) . "; width: 100%; height: 100%;'></div>";
				}
			}
			
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/** 
	 * Convert numeric value into color value based on a range
	 */
	 
	private function convertValueToRGB( $value ) {
		
		if( $value < 0 ) {
			$percentOf = $value / $this->min;
			$colorValue = round( $percentOf * 200, 0 );	
			return "rgb( 0, 0, " . ($colorValue + 55) . ")";
		} else if( $value > 0 ) {
			$percentOf = $value / $this->max;
			$colorValue = round( $percentOf * 200, 0 );	
			return "rgb( " . ($colorValue + 55) . ", 0, 0)";
		} 
		
		return "rgb( 0, 255, 0 )";
		
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
			$query .= "*";
		}
		
		$query .= " FROM " . DB_VIEWS . ".view_" . $this->view->view_code;
		
		$options = array( );
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " WHERE (group_name LIKE ? OR systematic_name LIKE ? OR aliases LIKE ?)";
			array_push( $options, '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%' );
		}
		
		return array( "QUERY" => $query, "OPTIONS" => $options );
			
	}
	
	/**
	 * Build a set of data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function buildCustomizedRowList( $params ) {
		
		$columnSet = $this->fetchColumnDefinitions(  );
		
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
			$rows[$row->sgrna_group_id] = $row;
		}
		
		return $rows;
		
	}
	
	/**
	 * Build a count of view data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function getUnfilteredCount( $params ) {
		
		$queryInfo = $this->buildDataTableQuery( $params, true );
		$query = $queryInfo['QUERY'];
		$options = $queryInfo['OPTIONS'];
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->rowCount;
		
	}
	
	/**
	 * Get a count of all rows available
	 */
	 
	public function fetchRowCount( ) {
		
		$stmt = $this->db->prepare( "SELECT COUNT(*) as rowCount FROM " . DB_VIEWS . ".view_" . $this->view->view_code );
		$stmt->execute( );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->rowCount;
		
	}
	
	/**
	 * Generate a select list of the available style options
	 */
	
	private function generateStyleSelectForToolbar( $currentStyle, $selectClass = "", $selectLabel = "" ) {
		$selectOptions = array( );
		
		$option = array( "SELECTED" => "", "NAME" => "Colors Only" );
		if( $currentStyle == 1 ) {
			$option["SELECTED"] = "selected";
		}	
		$selectOptions[1] = $option;
		
		$option = array( "SELECTED" => "", "NAME" => "Values Only" );
		if( $currentStyle == 2 ) {
			$option["SELECTED"] = "selected";
		}
		$selectOptions[2] = $option;
		
		$option = array( "SELECTED" => "", "NAME" => "Colors and Values" );
		if( $currentStyle == 3 ) {
			$option["SELECTED"] = "selected";
		}
		$selectOptions[3] = $option;
		$view = "blocks" . DS . "ORCADataTableToolbarSelect.tpl";
		
		$select = $this->twig->render( $view, array(
			"OPTIONS" => $selectOptions,
			"SELECT_CLASS" => $selectClass,
			"SELECT_LABEL" => $selectLabel
		));
		
		return $select;
		
	}
	
	/**
	 * Fetch a set of buttons for the view listing
	 * table toolbar
	 */
	
	public function fetchToolbar( $viewStyle ) {
		
		$buttons = array( );
		
		if( lib\Session::validateCredentials( lib\Session::getPermission( 'VIEW VIEWS' )) ) {
			$styleSelect = $this->generateStyleSelectForToolbar( $viewStyle, "pull-right col-lg-2 col-md-3 col-sm-4 col-xs-6", "Style:" );
			$buttons[] = $styleSelect;
		}
		
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
	 * Fetch formatted group annotation to be displayed in a popup tooltip
	 */
	 
	public function fetchFormattedGroupAnnotation( $groupID ) {
		
		$stmt = $this->db->prepare( "SELECT sgrna_group_reference, official_symbol, systematic_name, aliases, definition, organism_official_name FROM " . DB_VIEWS . ".view_" . $this->view->view_code . " WHERE sgrna_group_id=? LIMIT 1" );
		
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
				$aliases = explode( "|", $row->aliases );
				$annotationParams["Aliases"] = implode( ", ", $aliases );
			}
			
			if( $row->definition != "-" ) {
				$annotationParams["Definition"] = $row->definition;
			}
			
			if( $row->organism_official_name != "-" ) {
				$annotationParams["Organism"] = $row->organism_official_name;
			}
			
			$links = array( );
			$links["biogrid"] = models\LinkoutGenerator::getLinkout( "biogrid", $row->sgrna_group_reference );
			
			$annotation = $this->twig->render( "view" . DS . "ViewGroupAnnotation.tpl", array(
				"ANNOTATION" => $annotationParams,
				"LINKS" => $links
			));
			
			return $annotation;
		}
		
		return false;
		
	}
	
	/**
	 * Fetch a formatted list of files and background with links
	 * to view them on the separate file page
	 */
	 
	function fetchFormattedHeaderAnnotation( $fileID, $fileName, $bgID, $bgName ) {
			
		$files = array( );
		$files[$fileID] = array( "URL" => WEB_URL . "/Files/View?id=" . $fileID, "NAME" => $fileName, "LABEL" => "Primary File" );
		$files[$bgID] = array( "URL" => WEB_URL . "/Files/View?id=" . $bgID, "NAME" => $bgName, "LABEL" => "Background File" );
		
		$annotation = $this->twig->render( "view" . DS . "ViewFileHeader.tpl", array(
			"FILES" => $files
		));
		
		return $annotation;
		
	}
	
}

?>