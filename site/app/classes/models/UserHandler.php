<?php

namespace ORCA\app\classes\models;

/**
 * User Handler
 * This class is for handling processing of users
 */

use \PDO;
 
class UserHandler {

	private $db;

	public function __construct( ) {
		$this->db = new PDO( DB_CONNECT, DB_USER, DB_PASS );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}
	
	/**
	 * Build a list of all user ids, user names, emails, and first and last names, last login, class, status
	 */
	 
	public function buildUserList( ) {
		
		$users = array( );
		
		$stmt = $this->db->prepare( "SELECT user_id, user_name, user_firstname, user_lastname, user_email, user_lastlogin, user_class, user_status FROM " . DB_MAIN . ".users ORDER BY user_firstname ASC" );
		$stmt->execute( );
		
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$users[$row->user_id] = $row;
		}
		
		return $users;
	}
	
	/**
	 * Build a set of column header definitions for the manage users table
	 */
	 
	public function fetchManageUsersColumnDefinitions( ) {
		
		$columns = array( );
		$columns[0] = array( "title" => "ID", "data" => 0, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'user_id' );
		$columns[1] = array( "title" => "Name", "data" => 1, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'user_name' );
		$columns[2] = array( "title" => "First Name", "data" => 2, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'user_firstname' );
		$columns[3] = array( "title" => "Last Name", "data" => 3, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'user_lastname' );
		$columns[4] = array( "title" => "Email", "data" => 4, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'user_email' );
		$columns[5] = array( "title" => "Last Login", "data" => 5, "orderable" => true, "sortable" => true, "className" => "", "dbCol" => 'user_lastlogin' );
		$columns[6] = array( "title" => "Class", "data" => 6, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'user_class' );
		$columns[7] = array( "title" => "Status", "data" => 7, "orderable" => true, "sortable" => true, "className" => "text-center", "dbCol" => 'user_status' );
		$columns[8] = array( "title" => "Options", "data" => 8, "orderable" => false, "sortable" => false, "className" => "text-center", "dbCol" => '' );
		
		return $columns;
		
	}
	
	/**
	 * Build a set of user data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function buildCustomizedUserList( $params ) {
		
		$columnSet = $this->fetchManageUsersColumnDefinitions( );
		
		$users = array( );
		
		$query = "SELECT user_id, user_name, user_firstname, user_lastname, user_email, user_lastlogin, user_class, user_status FROM " . DB_MAIN . ".users";
		$options = array( );
		
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " WHERE user_id=? OR user_name LIKE ? OR user_firstname LIKE ? OR user_lastname LIKE ? OR user_email LIKE ? OR user_lastlogin=? OR user_class=? OR user_status=?";
			array_push( $options, $params['search']['value'], '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', $params['search']['value'], $params['search']['value'], $params['search']['value'] );
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
			$users[$row->user_id] = $row;
		}
		
		return $users;
		
	}
	
	/**
	 * Build a count of user data based on passed in parameters for searching
	 * and sorting of the results returned
	 */
	 
	public function getUnfilteredUsersCount( $params ) {
		
		$users = array( );
		
		$query = "SELECT count(*) as rowCount FROM " . DB_MAIN . ".users";
		$options = array( );
		
		if( isset( $params['search'] ) && strlen($params['search']['value']) > 0 ) {
			$query .= " WHERE user_id=? OR user_name LIKE ? OR user_firstname LIKE ? OR user_lastname LIKE ? OR user_email LIKE ? OR user_lastlogin=? OR user_class=? OR user_status=?";
			array_push( $options, $params['search']['value'], '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', '%' . $params['search']['value'] . '%', $params['search']['value'], $params['search']['value'], $params['search']['value'] );
		}
		
		$stmt = $this->db->prepare( $query );
		$stmt->execute( $options );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->rowCount;
		
	}
	
	/**
	 * Build out the options for the User Manager Table Field
	 */
	 
	private function buildUserManagerOptions( $userInfo ) {
		
		$options = array( );

		$options[] = "<i class='fa fa-arrow-up fa-lg popoverData promoteUser text-info' data-userid='" . $userInfo->user_id . "' data-toggle='popover' title='Promote this Account' data-content='Click this button to Increase this users access level' data-placement='top'></i>";
		$options[] = "<i class='fa fa-arrow-down fa-lg popoverData demoteUser text-primary' data-userid='" . $userInfo->user_id . "' data-toggle='popover' title='Demote this Account' data-content='Click this button to Decrease this users access level' data-placement='top'></i>";
		
		if( $userInfo->user_status == 'inactive' ) {
			$options[] = "<i class='fa fa-times fa-lg popoverData text-danger disableUser hide' data-userid='" . $userInfo->user_id . "' data-toggle='popover' title='Disable this Account' data-content='Click this button to disable this users access to the LIMS' data-placement='top'></i>";
		} else {
			$options[] = "<i class='fa fa-times fa-lg popoverData text-danger disableUser' data-userid='" . $userInfo->user_id . "' data-toggle='popover' title='Disable this Account' data-content='Click this button to disable this users access to the LIMS' data-placement='top'></i>";
		}
		
		if( $userInfo->user_status == 'active' ) {
			$options[] = "<i class='fa fa-check fa-lg popoverData text-success enableUser hide' data-userid='" . $userInfo->user_id . "' data-toggle='popover' title='Activate this Account' data-content='Click this button to re-enable this users access to the LIMS' data-placement='top'></i>";
		} else {
			$options[] = "<i class='fa fa-check fa-lg popoverData text-success enableUser' data-userid='" . $userInfo->user_id . "' data-toggle='popover' title='Activate this Account' data-content='Click this button to re-enable this users access to the LIMS' data-placement='top'></i>";
		}
		
		return implode( " ", $options );
		
	}
	
	/**
	 * Build a set of rows for the user manager
	 */
	 
	public function buildManageUserRows( $params ) {
		
		$userList = $this->buildCustomizedUserList( $params );
		$rows = array( );
		foreach( $userList as $userID => $userInfo ) {
			$column = array( );
			$column[] = $userID;
			$column[] = $userInfo->user_name;
			$column[] = $userInfo->user_firstname;
			$column[] = $userInfo->user_lastname;
			$column[] = $userInfo->user_email;
			$column[] = $userInfo->user_lastlogin;
			$column[] = $userInfo->user_class;
			$column[] = $userInfo->user_status;
			$column[] = $this->buildUserManagerOptions( $userInfo );
			$rows[] = $column;
		}
		
		return $rows;
		
	}
	
	/**
	 * Get a count of all users available
	 */
	 
	public function fetchUserCount( ) {
		
		$stmt = $this->db->prepare( "SELECT COUNT(*) as userCount FROM " . DB_MAIN . ".users" );
		$stmt->execute( );
		
		$row = $stmt->fetch( PDO::FETCH_OBJ );
		
		return $row->userCount;
		
	}
	
}

?>