<?php

/*

 Website Baker Project <http://www.websitebaker.org/>
 Copyright (C) 2004-2008, Ryan Djurovich

 Website Baker is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Website Baker is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Website Baker; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

require('../../config.php');

// Get ID
if(isset($_GET['entry_id']) AND is_numeric($_GET['entry_id']) &&
   isset($_GET['page_id']) AND is_numeric($_GET['page_id']) &&
	 isset($_GET['section_id']) AND is_numeric($_GET['section_id'])
) {
    $entry_id = $_GET['entry_id'];
    $page_id = $_GET['page_id'];
    $section_id = $_GET['section_id'];
} else {
    exit(header("Location: ".ADMIN_URL."/pages/index.php"));
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

// load geoip-database, if available
if(file_exists(WB_PATH.'/modules/geoip/geoip_functions.php')) {
	require_once(WB_PATH.'/modules/geoip/geoip_functions.php');
	$gi = geoip_open_db(); // defines constant GEOIP_DATABASE_LOADED on success
}
// load phpwhois
if(file_exists(WB_PATH.'/modules/geoip/phpwhois_functions.php')) {
	require_once(WB_PATH.'/modules/geoip/phpwhois_functions.php'); // defines constant WHOIS_LOADED on success
}

// check if backend.css file needs to be included into <body></body>
if(!method_exists($admin, 'register_backend_modfiles') && file_exists(WB_PATH ."/modules/guestbook/backend.css")) {
	echo '<style type="text/css">';
	include(WB_PATH .'/modules/guestbook/backend.css');
	echo "\n</style>\n";
}
// check if backend.js file needs to be included into <body></body>
if(!method_exists($admin, 'register_backend_modfiles') && file_exists(WB_PATH ."/modules/guestbook/backend.js")) {
	echo '<script type="text/javascript">';
	include(WB_PATH .'/modules/guestbook/backend.js');
	echo "</script>";
}

// check if module language file exists for the language set by the user (e.g. DE, EN)
if(file_exists(WB_PATH .'/modules/guestbook/languages/'.LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/guestbook/languages/'.LANGUAGE .'.php');
} else {
	require_once(WB_PATH .'/modules/guestbook/languages/EN.php');
}

// fetch settings
$query_settings = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_guestbook_settings` WHERE `section_id` = '$section_id'");
$settings = $query_settings->fetchRow();

// display the guestbook entry
$query = "SELECT *, INET_NTOA(`ip_addr`) AS `ip_addr` FROM `".TABLE_PREFIX."mod_guestbook` WHERE `page_id` = '$page_id' AND `id` = '$entry_id'";
$query_entry = $database->query($query);

if($query_entry->numRows() > 0) {
	$entry = $query_entry->fetchRow();
	$date_string = gmdate(DATE_FORMAT, $entry['posted_when']+TIMEZONE);

	if($settings['textarea_style']==1) { // autogrow
		echo '<script type="text/javascript">$(document).ready(function(){$("textarea").growfield();});</script>';
	}

	// Template
	$t = new Template(dirname(__FILE__).'/templates', 'remove');
	$t->halt_on_error = 'no';
	$t->set_file('entry', 'gstbk_modify.htt');
	// clear the comment-block, if present
	 $t->set_block('entry', 'CommentDoc'); $t->clear_var('CommentDoc');
	$t->set_var(array(
		'HIDDEN' => 'style="display:none !important;"',
		'MODIFY_STRING' => $TEXT['MESSAGE'].' / '.$TEXT['MODIFY'],
		'MODSAVE_URL' => WB_URL.'/modules/guestbook/gstbk_modsav.php',
		'PAGE_ID_VALUE' => $page_id,
		'SECTION_ID_VALUE' => $section_id,
		'ENTRY_ID_VALUE' => $entry['id'],
		'POSTED_WHEN_VALUE' => $entry['posted_when'],
		'POSITION_VALUE' => $entry['position'],
		'NAME_STRING' => $TEXT['NAME'],
		'NAME' => $entry['name'],
		'DATE_STRING' => $TEXT['DATE'],
		'DATE' => $date_string,
		'IP_STRING' => 'IP',
		'EMAIL_STRING' => $TEXT['EMAIL'],
		'EMAIL' => $entry['email'],
		'WEBSITE_STRING' => $TEXT['WEBSITE'],
		'WEBSITE' => $entry['homepage'],
		'MESSAGE_STRING' => $TEXT['MESSAGE'],
		'MESSAGE' => $entry['message'],
		'COMMENT_STRING' => $TEXT['COMMENT'],
		'COMMENT' => $entry['comment'],
		'SUBMIT' => $MOD_GUESTBOOK['SUBMIT'],
		'CANCEL' => $TEXT['CANCEL'],
		'CANCEL_ONCLICK' => 'javascript: window.location = \''.ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'\''
	));	
	if(defined('GEOIP_DATABASE_LOADED')) {
		$cn = geoip_country_name_by_addr($gi, $entry['ip_addr']);
		if($cn=='') $cn = '??';
		$t->set_var('COUNTRY', ' <small>('.$cn.')</small>');
	} else {
		$t->set_var('COUNTRY', '');
	}
	if(defined('WHOIS_LOADED')) {
		$t->set_var('IP', geoip_whois_link($entry['ip_addr']));
	} else {
		$t->set_var('IP', $entry['ip_addr']);
	}

	$t->pparse('Output', 'entry');
}

// Print admin footer
$admin->print_footer();
