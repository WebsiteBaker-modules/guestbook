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

// prevent this file from being accessed directly
if(!defined('WB_PATH')) die(header('Location: ../../index.php'));  

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
if(file_exists(WB_PATH.'/modules/geoip/phpwhois_functions.php')) {
	require_once(WB_PATH.'/modules/geoip/phpwhois_functions.php'); // defines constant WHOIS_LOADED on success
}

// fetch settings
$query_settings = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_guestbook_settings` WHERE `section_id` = '$section_id'");
$settings = $query_settings->fetchRow();
if($settings['ordering'] == '0') {
	$order = 'ASC';
} else {
	$order = 'DESC';
}

// Template
$t = new Template(dirname(__FILE__).'/templates', 'remove');
$t->halt_on_error = 'no';
$t->set_file('messages', 'modify.htt');
// clear the comment-block, if present
 $t->set_block('messages', 'CommentDoc'); $t->clear_var('CommentDoc');
$t->set_block('messages', 'Move_Up', 'MOVEUP');
$t->set_block('messages', 'Move_Down', 'MOVEDOWN');
$t->set_block('messages', 'Message_Row', 'ROW');
$t->set_var(array(
	'HIDDEN' => 'style="display:none !important;"',
	'SETTINGS_STRING' => $TEXT['SETTINGS'],
	'SETTINGS_ONCLICK' => 'javascript: window.location = \''.WB_URL.'/modules/guestbook/modify_settings.php?page_id='.$page_id.'&amp;section_id='.$section_id.'\';',
	'HEADING_STRING' => $TEXT['VIEW'].'/'.$TEXT['DELETE'].' '.$TEXT['MESSAGE'],
	'VIEW_STRING' => $TEXT['VIEW'],
	'MODIFY_STRING' => $TEXT['MODIFY'],
	'COMMENT_STRING' => $TEXT['COMMENT'],
	'DATE_STRING' => $TEXT['DATE'],
	'IP_STRING' => 'IP',
	'MOVEUP_STRING' => $TEXT['MOVE_UP'],
	'MOVEDOWN_STRING' => $TEXT['MOVE_DOWN'],
	'DELETE_STRING' => $TEXT['DELETE'],
	'ASK_STRING' => $TEXT['ARE_YOU_SURE'],
	'WB_URL' => WB_URL,
	'THEME_URL' => THEME_URL
));

// Loop through existing guestbook entries
$query_entries = $database->query("SELECT *, INET_NTOA(`ip_addr`) AS `user_ip` FROM `".TABLE_PREFIX."mod_guestbook` WHERE `section_id` = '$section_id' ORDER BY position $order");
$num_entries = $query_entries->numRows();
if($num_entries>0) {
	$row = 'a';
	while($entry = $query_entries->fetchRow()) {
		$t->set_var(array(
			'CLASS_ROW' => 'row_'.$row,
			'VIEW_LINK' => WB_URL.'/modules/guestbook/view_entry.php?page_id='.$page_id.'&amp;section_id='.$section_id.'&amp;entry_id='.$entry['id'],
			'MODIFY_LINK' => WB_URL.'/modules/guestbook/gstbk_modify.php?page_id='.$page_id.'&amp;section_id='.$section_id.'&amp;entry_id='.$entry['id'],
			'MESSAGE' => $entry['message'],
			'DATE' => gmdate(DATE_FORMAT, $entry['posted_when']+TIMEZONE),
			'DELETE_LINK' => WB_URL.'/modules/guestbook/delete_entry.php?page_id='.$page_id.'&amp;section_id='.$section_id.'&amp;entry_id='.$entry['id']));
		if ($entry['comment'])
			$t->set_var('COMMENT', $entry['comment']);
		else
			$t->set_var('COMMENT','---');
		if(defined('WHOIS_LOADED')) {
			$t->set_var('IP', geoip_whois_link($entry['user_ip']));
		} else {
			$t->set_var('IP', $entry['user_ip']);
		}
		if(defined('GEOIP_DATABASE_LOADED'))
			$t->set_var('FLAG', geoip_flag_html($gi, $entry['user_ip']));
		else
			$t->set_var('FLAG', '');
		if($entry['approved']==1) {
			$t->set_var(array(
				'APPROVED_LINK' => WB_URL.'/modules/guestbook/approve_entry.php?page_id='.$page_id.'&amp;entry_id='.$entry['id'].'&amp;approve=0',
				'APPROVED_STRING' => 'Approved',
				'UN' => ''));
		} else {
			$t->set_var(array(
				'APPROVED_LINK' => WB_URL.'/modules/guestbook/approve_entry.php?page_id='.$page_id.'&amp;entry_id='.$entry['id'].'&amp;approve=1',
				'APPROVED_STRING' => 'Unapproved',
				'UN' => 'un'));
		}
		if($entry['position'] != 1) {
			$t->set_var('MOVEUP_LINK', WB_URL.'/modules/guestbook/move_up.php?page_id='.$page_id.'&amp;section_id='.$section_id.'&amp;entry_id='.$entry['id']);
			$t->parse('MOVEUP','Move_Up');
		} else {
			$t->set_var(array(
				'MOVEUP_LINK' => '',
				'MOVEUP' => ''));
		}
		if($entry['position'] != $num_entries) {
			$t->set_var('MOVEDOWN_LINK', WB_URL.'/modules/guestbook/move_down.php?page_id='.$page_id.'&amp;section_id='.$section_id.'&amp;entry_id='.$entry['id']);
			$t->parse('MOVEDOWN','Move_Down');
		} else {
			$t->set_var(array(
				'MOVEDOWN_LINK' => '',
				'MOVEDOWN' => ''));
		}
		$t->parse('ROW','Message_Row', TRUE);
		// Alternate row color
		if($row == 'a') $row = 'b';
		else $row = 'a';
	}
	$t->pparse('Output', 'messages');
} else {
	// say "No Entries"
	$t->set_var('ROW','');
	$t->pparse('Output', 'messages');
	echo $TEXT['NONE_FOUND'];
}

// close geoip-database
if(defined('GEOIP_DATABASE_LOADED'))
	geoip_close($gi);
