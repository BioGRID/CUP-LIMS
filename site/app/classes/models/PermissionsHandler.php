<?php

namespace ORCA\app\classes\models;

/**
 * Permissions Handler
 * This class is for handling processing of permissions
 */

use \PDO;
use ORCA\app\lib;
 
class PermissionsHandler {

	private $db;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->permissions = array( "public", "observer", "curator", "poweruser", "admin" );
	}
	
	
	/**
	 * Build a set of column header definitions for the manage permissions table
	 */
	 
	public function fetchManagePermissionsColumnDefinitions( ) {
		
		$columns = array( );
		$columns[0] = array( "title" => "ID", "data" => 0, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'permission_id' );
		$columns[1] = array( "title" => "Name", "data" => 1, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'permission_name' );
		$columns[2] = array( "title" => "Description", "data" => 2, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'permission_desc' );
		$columns[3] = array( "title" => "Category", "data" => 3, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'permission_category' );
		$columns[4] = array( "title" => "Permission Setting", "data" => 4, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
		
		return $columns;
		
	}
	
	/**
	 * Build a set of permission data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function buildCustomizedPermissionsList( $params ) {
		
		$columnSet = $this->fetchManagePermissionsColumnDefinitions( );
		
		$users = array( );
		
		$query = "SELECT permission_id, permission_name, permission_desc, permission_level, permission_category FROM " . DB_MAIN . ".permissions";
		$options = array( );
		
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " WHERE permission_id=? OR permission_name LIKE ? OR permission_desc LIKE ? OR permission_category LIKE ?";
			array_push( $options, $params['search']['value'], '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%' );
		}
		
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
			$users[$row->permission_id] = $row;
		}
		
		return $users;
		
	}
	
	/**
	 * Build a count of permission data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function getUnfilteredPermissionsCount( $params ) {
		
		$users = array( );
		
		$query = "SELECT count(*) as rowCount FROM " . DB_MAIN . ".permissions";
		$options = array( );
		
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " WHERE permission_id=? OR permission_name LIKE ? OR permission_desc LIKE ? OR permission_category LIKE ?";
			array_push( $options, $params['search']['value'], '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%' );
		}
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->rowCount;
		
	}
	
	/**
	 * Build out the options for the Permissions Manager Table Field
	 */
	 
	private function buildManagePermissionsOptions( $permInfo ) {
		
		$options = array( );

		foreach( $this->permissions as $permission ) {
			$optionName = "permissionOption" . $permInfo->permission_id;
			$options[] = "<label class='radio-inline'><input class='permissionChange' type='radio' name='" . $optionName . "' id='" . $optionName . "' data-permission='" . $permInfo->permission_id . "' value='" . $permission . "'" . (($permission==$permInfo->permission_level)? " checked='checked'" : "") . " />" . $permission . "</label>";
		}
		
		return implode( " ", $options );
		
	}
	
	/**
	 * Build a set of rows for the permissions manager
	 */
	 
	public function buildManagePermissionsRows( $params ) {
		
		$permList = $this->buildCustomizedPermissionsList( $params );
		$rows = array( );
		foreach( $permList as $permID => $permInfo ) {
			$column = array( );
			$column[] = $permID;
			$column[] = $permInfo->permission_name;
			$column[] = $permInfo->permission_desc;
			$column[] = $permInfo->permission_category;
			$column[] = $this->buildManagePermissionsOptions( $permInfo );
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/**
	 * Change permission level for a given permission
	 */
	 
	public function changePermissionLevel( $permissionID, $newLevel ) {
		
		if( lib\Session::validateCredentials( lib\Session::getPermission( 'MANAGE PERMISSIONS' ))) {
			$stmt = $this->db->prepare( "UPDATE " . DB_MAIN . ".permissions SET permission_level=? WHERE permission_id=?" );
			$stmt->execute( array( $newLevel, $permissionID ));
			return true;
		}
		
		return false;
		
	}
	
	/**
	 * Get a count of all permissions available
	 */
	 
	public function fetchPermissionCount( ) {
		
		$stmt = $this->db->prepare( "SELECT COUNT(*) as permCount FROM " . DB_MAIN . ".permissions" );
		$stmt->execute( );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->permCount;
		
	}
	
	/**
	 * Get the list of permissions
	 */
	 
	public function getPermissionList( ) {
		return $this->permissions;
	}
	
	/**
	 * Add a new permission to the database with a default
	 * setting for the permission level
	 */
	 
	public function addPermission( $permissionName, $permissionDesc, $permissionLevel, $permissionCategory ) {
		
		$permissionName = strtoupper( trim($permissionName) );
		$permissionCategory = strtoupper( trim($permissionCategory) );
		
		$stmt = $this->db->prepare( "SELECT permission_id FROM " . DB_MAIN . ".permissions WHERE permission_name=? AND permission_category=? LIMIT 1" );
		$stmt->execute( array( $permissionName, $permissionCategory ));
		
		if( $stmt->rowCount( ) > 0 ) {
			return false;
		}
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		if( lib\Session::validateCredentials( lib\Session::getPermission( 'MANAGE PERMISSIONS' ))) {
			$stmt = $this->db->prepare( "INSERT INTO " . DB_MAIN . ".permissions VALUES( '0', ?, ?, ?, ? )" );
			$stmt->execute( array( $permissionName, $permissionDesc, $permissionCategory, $permissionLevel  ));
			return true;
		}
	
		return false;
		
	}
	
}

?>