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
if(isset($_GET['entry_id']) AND is_numeric($_GET['entry_id'])) {
	$entry_id = $_GET['entry_id'];
} else {
	exit(header("Location: ".ADMIN_URL."/pages/index.php"));
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

// check if backend.css file needs to be included into <body></body>
if(!method_exists($admin, 'register_backend_modfiles') && file_exists(WB_PATH ."/modules/guestbook/backend.css")) {
	echo '<style type="text/css">';
	include(WB_PATH .'/modules/guestbook/backend.css');
	echo "\n</style>\n";
}

// load geoip-database, if available
if(file_exists(WB_PATH.'/modules/geoip/geoip_functions.php')) {
	require_once(WB_PATH.'/modules/geoip/geoip_functions.php');
	$gi = geoip_open_db(); // defines constant GEOIP_DATABASE_LOADED on success
}
// load phpwhois
if(file_exists(WB_PATH.'/modules/geoip/phpwhois_functions.php')) {
	require_once(WB_PATH.'/modules/geoip/phpwhois_functions.php'); // defines constant WHOIS_LOADED on success
}

// display the guestbook entry
$query = "SELECT *, `ip_addr` AS `user_ip` FROM `".TABLE_PREFIX."mod_guestbook` WHERE `page_id` = '$page_id' AND `section_id` = '$section_id' AND `id` = '$entry_id'";
$query_entry = $database->query($query);
if($query_entry->numRows() > 0) {
	$entry = $query_entry->fetchRow();
	// Template
	$t = new Template(dirname(__FILE__).'/templates', 'remove');
	$t->halt_on_error = 'no';
	$t->set_file('entry', 'view_entry.htt');
	// clear the comment-block, if present
	 $t->set_block('entry', 'CommentDoc'); $t->clear_var('CommentDoc');
	$t->set_var(array(
		'HIDDEN' => 'style="display:none !important;"',
		'SHOW_STRING' => $TEXT['MESSAGE'].' / '.$TEXT['SHOW'],
		'NAME_STRING' => $TEXT['NAME'],
		'NAME' => $entry['name'],
		'EMAIL_STRING' => $TEXT['EMAIL'],
		'EMAIL' => $entry['email'],
		'DATE_STRING' => $TEXT['DATE'],
		'DATE' => gmdate(DATE_FORMAT, $entry['posted_when']+TIMEZONE).' - '.gmdate(TIME_FORMAT, $entry['posted_when']+TIMEZONE),
		'WEBSITE' => $TEXT['WEBSITE'],
		'HOMEPAGE' => $entry['homepage'],
		'MESSAGE_STRING' => $TEXT['MESSAGE'],
		'MESSAGE' => $entry['message'],
		'COMMENT_STRING' => $TEXT['COMMENT'],
		'COMMENT' => $entry['comment'],
		'DELETE_STRING' => $TEXT['DELETE'],
		'CANCEL_STRING' => $TEXT['CANCEL'],
		'DELETE_ONKLICK' => 'javascript: confirm_link(\''.$TEXT['ARE_YOU_SURE'].'\', \''.WB_URL.'/modules/guestbook/delete_entry.php?page_id='.$page_id.'&amp;section_id='.$section_id.'&amp;entry_id='.$entry['id'].'\');',
		'CANCEL_ONKLICK' => 'javascript: window.location = \''.ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'\'',
		'SERVER_VARS' => ($entry['server_vars']?'<br />'.$entry['server_vars']:'&nbsp;'),
		'SERVER_VARS_STRING' => ($entry['server_vars']?'<br />$_SERVER:':'&nbsp;')
	));
	if(defined('GEOIP_DATABASE_LOADED')) {
		$cn = geoip_country_name_by_addr($gi, $entry['user_ip']);
		if($cn=='') $cn = '??';
		$t->set_var('COUNTRY', ' <small>('.$cn.')</small>');
	} else {
		$t->set_var('COUNTRY', '');
	}
	if(defined('WHOIS_LOADED')) {
		$t->set_var('IP', geoip_whois_link($entry['user_ip']));
	} else {
		$t->set_var('IP', $entry['user_ip']);
	}
	
	$t->pparse('Output', 'entry');
}

// Print admin footer
$admin->print_footer();
