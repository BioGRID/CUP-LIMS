<?php

namespace ORCA\app\inc;

/**
 * Config Options
 * Below are a set of options used throughout the web application. Most of these variables should not be modified
 * directly. Instead, modify the config/config.json variable in the root of the application, and variables located 
 * here will update in kind.
 */
 
 /** 
 * Error Handling
 * Enable or Disable errors, useful for when testing new
 * code or in development of new features
 */

define( "DEBUG", true );

if( DEBUG ) {
	error_reporting( E_ALL );
	ini_set( 'display_errors', 'On' );
} else {
	error_reporting( E_ALL );
	ini_set( 'display_errors', 'Off' );
	ini_set( 'log_errors', 'On' );
}

/**
 * Parse configuration from JSON if not currently available
 * in the Session
 */
 
$config = array( );
if( !isset( $_SESSION['ORCA_CONFIG'] ) && isset( $_SESSION['ORCA_CONFIG']['DB'] )) {
	$config = $_SESSION['ORCA_CONFIG'];
} else {
	$config = trim(file_get_contents( '../../config/config.json' ));
	$config = json_decode( $config, true );
	$_SESSION['ORCA_CONFIG'] = $config;
}

if( $config == NULL ) {
	die( "ERROR PARSING CONFIG FILE, PLEASE ENSURE IT'S BOTH ACCESSIBLE AND HAS NO SYNTAX ERRORS. TO TEST FOR SYNTAX ERRORS, TRY: http://jsonlint.com" );
}


define( 'CONFIG', $config );

/**
 * Initialize Config Variables
 * These are regularly used variables made with a combination
 * of options loaded from the config file. Do not modify these
 * directly.
 */


/**
 * PDO MySQL Connection String and DB Variables
 */

define( 'DB_CONNECT', 'mysql:host=' . CONFIG['DATABASE']['DB_HOST'] . ';port=' . CONFIG['DATABASE']['DB_PORT'] . ';dbname=' . CONFIG['DATABASE']['DB_ORCA'] . ';charset=utf8' );

define( 'DB_USER', CONFIG['DATABASE']['DB_USER'] );
define( 'DB_PASS', CONFIG['DATABASE']['DB_PASS'] );
define( 'DB_MAIN', CONFIG['DATABASE']['DB_ORCA'] );
define( 'DB_QUICK', CONFIG['DATABASE']['DB_QUICK'] );

/**
 * PATHS
 */
 
define( 'WEB_PATH', CONFIG['DIRECTORIES']['BASE_PATH'] . "/" . CONFIG['DIRECTORIES']['WEB_DIR'] );
define( 'APP_PATH', CONFIG['DIRECTORIES']['BASE_PATH'] . "/" . CONFIG['DIRECTORIES']['APP_DIR'] );
define( 'TEMPLATE_PATH', APP_PATH . "/" . CONFIG['DIRECTORIES']['TEMPLATE_DIR'] );
define( 'INC_PATH', APP_PATH . "/" . CONFIG['DIRECTORIES']['INC_DIR'] );

/**
 * URLS
 */
 
define( 'WEB_URL', CONFIG['WEB']['WEB_URL'] );
define( 'CSS_URL', WEB_URL . "/" . CONFIG['DIRECTORIES']['CSS_DIR'] );
define( 'JS_URL', WEB_URL . "/" . CONFIG['DIRECTORIES']['JS_DIR'] );
define( 'IMG_URL', WEB_URL . "/" . CONFIG['DIRECTORIES']['IMG_DIR'] );
define( 'SCRIPT_URL', WEB_URL . "/" . CONFIG['DIRECTORIES']['SCRIPT_DIR'] );

/**
 * SESSION NAME
 */
 
define( 'SESSION_NAME', CONFIG['SESSION']['SESSION_NAME'] );

?>