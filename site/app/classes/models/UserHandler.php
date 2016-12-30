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
	 * Build a list of all user ids, user names, emails, and first and last names
	 */
	 
	public function buildUserList( ) {
		
		$users = array( );
		
		$stmt = $this->db->prepare( "SELECT user_id, user_name, user_firstname, user_lastname, user_email FROM " . DB_MAIN . ".users ORDER BY user_firstname ASC" );
		$stmt->execute( );
		
		while( $row = $stmt->fetch( PDO::FETCH_OBJ ) ) {
			$users[$row->user_id] = $row;
		}
		
		return $users;
	}
	
}

?>