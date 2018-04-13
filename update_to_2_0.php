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

if (defined('WB_URL')) {

	$settingstable=$database->query("SELECT * FROM `".TABLE_PREFIX."mod_guestbook_settings");
	$settings = $settingstable->fetchRow();
	$gbtable=$database->query("SELECT * FROM `".TABLE_PREFIX."mod_guestbook");
	$guestbook = $gbtable->fetchRow();

	
	// STEP 1:		Add field to guestbook settings table for auto-approval
	if(!isset($settings['auto_approve'])){
		if($database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook_settings` ADD `auto_approve` TINYINT NOT NULL DEFAULT '0'")) {
			echo '<span class="good">Database Field auto_approve added successfully</span><br />';
		}
			echo '<span class="bad">'.mysql_error().'</span><br />';
	} else {
		echo '<span class="ok">Database Field auto_approve exists update not needed</span><br />';
	}

	// STEP 2:		Add field to guestbook table to record if a post has been approved or not
	if(!isset($guestbook['approved'])){
		if($database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook` ADD `approved` TINYINT NOT NULL DEFAULT '0'")) {
			echo '<span class="good">Database Field approved added successfully</span><br />';
		}
			echo '<span class="bad">'.mysql_error().'</span><br />';
	} else {
		echo '<span class="ok">Database Field approved exists update not needed</span><br />';
	}

	// STEP 3:		Add field to guestbook table to save admin email 
	if(!isset($settings['admin_email'])){
		if($database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook_settings` ADD `admin_email` TEXT NOT NULL")) {
			echo '<span class="good">Database Field admin_email added successfully</span><br />';
		}
			echo '<span class="bad">'.mysql_error().'</span><br />';
	} else {
		echo '<span class="ok">Database Field admin_email exists update not needed</span><br />';
	}
	
	// STEP 4:		Add field to guestbook table for to show smileys at frontend
	if(!isset($settings['show_smiley'])){
		if($database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook_settings` ADD `show_smiley` TINYINT(1) NOT NULL DEFAULT '0'")) {
			echo '<span class="good">Database Field show_smiley added successfully</span><br />';
		}
			echo '<span class="bad">'.mysql_error().'</span><br />';
	} else {
		echo '<span class="ok">Database Field show_smiley exists update not needed</span><br />';
	}

	if($database->is_error()) {
		echo ("OOPS, something went wrong. If it's a duplicate error then it's okay - it means that your database has already been modified.<br/>The error was: ".$database->get_error());
	} else {
		echo ("SUCCESS: The required changes have been made to your database.");
	}
	
	// STEP 5: 		Insert default values to guestbook settings
	$auto_approve = 1;
	$show_smiley = 1;
	
	$query_dates = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_guestbook_settings where section_id != 0 and page_id != 0");
	while($result = $query_dates->fetchRow()) {

		echo "<br /><b>Add default value to guestbook settings where section_id= ".$result['section_id']."</b><br />";
		$section_id = $result['section_id'];

		if($database->query("UPDATE `".TABLE_PREFIX."mod_guestbook_settings` SET `auto_approve` = '$auto_approve' WHERE `section_id` = $section_id")) {
		echo '<span class="good">Database data auto_approve added successfully</span> - ';
		}
		echo '<span class="bad">'.mysql_error().'</span><br />';
		
		if($database->query("UPDATE `".TABLE_PREFIX."mod_guestbook_settings` SET `show_smiley` = '$show_smiley' WHERE `section_id` = $section_id")) {
		echo '<span class="good">Database data auto_approve added successfully</span> - ';
		}
		echo '<span class="bad">'.mysql_error().'</span><br />';
	}

	// STEP 6:		 Insert default approved values to guestbook entries
	$approved = 1;
	
	$query_dates = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_guestbook where section_id != 0 and page_id != 0");
	while($result = $query_dates->fetchRow()) {

		echo "<br /><b>Add default value for field approved to guestbook entries section_id= ".$result['section_id']."</b><br />";
		$section_id = $result['section_id'];

		if($database->query("UPDATE `".TABLE_PREFIX."mod_guestbook` SET `approved` = '$approved' WHERE `section_id` = $section_id")) {
		echo '<span class="good">Database data approved added successfully</span> - ';
		}
		echo '<span class="bad">'.mysql_error().'</span><br />';
	}

}

