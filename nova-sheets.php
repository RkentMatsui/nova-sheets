<?php
/*
Plugin Name: Nova Google Sheets Integration
Plugin URI:  http://novasignage.com
Description: Integrates Google Sheets API to sync partners
Version:     1.1
Author:      Bonn Joel Elimanco <bonnjoel@gmail.com>
Author URI:  https://www.onlinejobs.ph/jobseekers/info/77592
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/class-nova-google-sheets-integration.php';
require_once __DIR__ . '/classes/class-nova-partner-data.php';
require_once __DIR__ . '/classes/class-nova-sheets-client.php';
// require_once get_stylesheet_directory() . '/vendor/autoload.php';
require __DIR__ . '/bonn-update-checker/plugin-update-checker.php';

use Bonn\PluginUpdateChecker\v5\PucFactory;

$nova_update_checker = PucFactory::buildUpdateChecker(
    'https://github.com/RkentMatsui/nova-sheets/',
    __FILE__,
    'nova-sheets'
);
function run_google_sheets_integration() {
	new Nova_Google_Sheets_Integration();
}
add_action( 'plugins_loaded', 'run_google_sheets_integration' );
