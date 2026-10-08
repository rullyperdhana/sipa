<?php
/**
 * SiTraSu — Sistem Tracking Surat Menyurat
 * CodeIgniter 3 Entry Point
 */
	define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

// The directory name of the "application" folder.
$application_folder = 'application';

// The directory name of the "system" folder.
$system_path = 'system';

// The $routing array should be set by auto-detecting this file's path.
$routing['directory']  = '';
$routing['controller'] = '';
$routing['function']   = '';

// ----------------------------------------------------------------

/*
 * CODEIGNITER'S ENTRY POINT (do not modify below unless you know what you're doing)
 */

// CodeIgniter version
//define('CI_VERSION', '3.1.13');

/*
 * ---------------------------------------------------------------
 *  Grab the system path and resolve it
 * ---------------------------------------------------------------
 */
if (($_temp = realpath($system_path)) !== FALSE) {
    $system_path = $_temp.DIRECTORY_SEPARATOR;
} else {
    // Ensure there's a trailing slash
    $system_path = rtrim($system_path, '/\\').DIRECTORY_SEPARATOR;
}

// Is the system path correct?
if ( ! is_dir($system_path)) {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
    exit(3);
}

/*
 * ---------------------------------------------------------------
 *  Resolve the application path
 * ---------------------------------------------------------------
 */
if (($_temp = realpath($application_folder)) !== FALSE) {
    $application_folder = $_temp.DIRECTORY_SEPARATOR;
} else {
    $application_folder = rtrim($application_folder, '/\\').DIRECTORY_SEPARATOR;
}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
define('SELF',        pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH',    $system_path);
define('FCPATH',      dirname(__FILE__).DIRECTORY_SEPARATOR);
define('SYSPATH',     $system_path);
define('APPPATH',     $application_folder);
define('VIEWPATH',    APPPATH.'views'.DIRECTORY_SEPARATOR);

/*
 * ---------------------------------------------------------------
 *  Load the global functions
 * ---------------------------------------------------------------
 */
require_once BASEPATH.'core/CodeIgniter.php';
