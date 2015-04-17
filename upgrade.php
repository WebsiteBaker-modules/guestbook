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

if(!defined('WB_PATH')) { exit("Cannot access this file directly"); }


// #######################################################################
// VERSION < 2.5
if(version_compare($module_version, '2.5', '<')) {

	//Remove stylesheets from database
	$header = '<div align=\"center\">[ADD_ENTRY]<br /><br /></div>';
	$query_dates = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_guestbook_settings where section_id != 0 and page_id != 0");
	while($result = $query_dates->fetchRow()) {
		$section_id = $result['section_id'];
		$database->query("UPDATE `".TABLE_PREFIX."mod_guestbook_settings` SET `header` = '$header' WHERE `section_id` = $section_id");
	}
	
	// Creating the search settings again
	$database->query("DELETE FROM ".TABLE_PREFIX."search WHERE name = 'module' AND value = 'guestbook'");
	$database->query("DELETE FROM ".TABLE_PREFIX."search WHERE extra = 'guestbook'");
		
	// Module query info
	$field_info = array();
	$field_info['page_id'] = 'page_id';
	$field_info['title'] = 'page_title';
	$field_info['link'] = 'link';
	$field_info = serialize($field_info);
	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('module', 'guestbook', '$field_info')");
	//Query start
	$query_start_code = "SELECT [TP]pages.page_id, [TP]pages.page_title, [TP]pages.link FROM [TP]mod_guestbook, [TP]pages WHERE ";
	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('query_start', '$query_start_code', 'guestbook')");
	//Query body
	$query_body_code = "
	[TP]pages.page_id = [TP]mod_guestbook.page_id AND [TP]mod_guestbook.name [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
	[TP]pages.page_id = [TP]mod_guestbook.page_id AND [TP]mod_guestbook.email [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
	[TP]pages.page_id = [TP]mod_guestbook.page_id AND [TP]mod_guestbook.homepage [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
	[TP]pages.page_id = [TP]mod_guestbook.page_id AND [TP]mod_guestbook.message [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\'
	";	
	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('query_body', '$query_body_code', 'guestbook')");
	//Query end
	$query_end_code = "";	
	$database->query("INSERT INTO ".TABLE_PREFIX."search (name,value,extra) VALUES ('query_end', '$query_end_code', 'guestbook')");
	
	//Add empty rows [ Insert blank rows (there needs to be at least on row for the search to work) ]
	$database->query("INSERT INTO ".TABLE_PREFIX."mod_guestbook (section_id,page_id) VALUES ('0', '0')");
	$database->query("INSERT INTO ".TABLE_PREFIX."mod_guestbook_settings (section_id,page_id) VALUES ('0', '0')");
	if($database->is_error()) echo 'Error: '.$database->get_error();
}


// #######################################################################
// VERSION < 2.7.03
if($module_version<'2.7.03') {
	// strip slashes
	$query_dates = $database->query("SELECT `header`,`gbk_loop`,`footer`,`section_id` FROM `".TABLE_PREFIX."mod_guestbook_settings` where `section_id` != 0 and `page_id` != 0");
	while($result = $query_dates->fetchRow()) {
		$section_id = $result['section_id'];
		$header = addslashes(stripslashes($result['header']));
		$gbk_loop = addslashes(stripslashes($result['gbk_loop']));
		$footer = addslashes(stripslashes($result['footer']));
		$database->query("UPDATE `".TABLE_PREFIX."mod_guestbook_settings` SET `header`='$header',`gbk_loop`='$gbk_loop',`footer`='$footer' WHERE `section_id`=$section_id");
		if($database->is_error()) echo 'Error: '.$database->get_error();
	}
	// add field gbk_loop_b
	$query_dates = $database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook_settings` ADD `gbk_loop_b` TEXT NOT NULL AFTER `gbk_loop`");
	if($database->is_error()) echo 'Error: '.$database->get_error();
$gbk_loop = '<table class="gbentry">
 <tr>
  <td class="gbtitle">[NAME] [EMAIL] [HOMEPAGE]</td>
  <td class="gbtitle" align="right">[DATE] [TIME]</td>
 </tr>
 <tr>
  <td colspan="2" class="gbcontent">[MESSAGE]
    <div class="gbcomment">[COMMENT]</div>
  </td>
 </tr>
</table>';
$gbk_loop_b = '<table class="gbentry_b">
 <tr>
  <td class="gbtitle">[NAME] [EMAIL] [HOMEPAGE]</td>
  <td class="gbtitle" align="right">[DATE] [TIME]</td>
 </tr>
 <tr>
  <td colspan="2" class="gbcontent">[MESSAGE]
    <div class="gbcomment">[COMMENT]</div>
  </td>
 </tr>
</table>';
$gbk_loop_old = '<table class=\"gbentry\">
 <tr>
  <td class=\"gbtitle\">[NAME] [EMAIL] [HOMEPAGE]</td>
  <td class=\"gbtitle\" align=\"right\">[DATE] [TIME]</td>
 </tr>
 <tr>
  <td colspan=\"2\" class=\"gbcontent\">[MESSAGE]</td>
 </tr>
</table>';
	$gbk_loop = addslashes($gbk_loop);
	$gbk_loop_b = addslashes($gbk_loop_b);
	$gbk_loop_old = addslashes($gbk_loop_old);
	// add field `gbk_loop_b`
	$database->query("UPDATE `".TABLE_PREFIX."mod_guestbook_settings` SET `gbk_loop_b`='$gbk_loop_b' WHERE `gbk_loop_b`='' AND `section_id`!=0");
	if($database->is_error()) echo 'Error: '.$database->get_error();
	// update field `gbk_loop` if possible
	$database->query("UPDATE `".TABLE_PREFIX."mod_guestbook_settings` SET `gbk_loop`='$gbk_loop' WHERE `gbk_loop`='$gbk_loop_old' AND `section_id`!=0");
	if($database->is_error()) echo 'Error: '.$database->get_error();
	// add field `comment`
	$database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook` ADD `comment` TEXT NOT NULL AFTER `message`");
	if($database->is_error()) echo 'Error: '.$database->get_error();
}


// #######################################################################
// VERSION < 2.7.05
if($module_version<'2.7.05') {
	// add field no_entries
	$query_dates = $database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook_settings` ADD `no_entries` TEXT NOT NULL");
	if($database->is_error()) echo 'Error: '.$database->get_error();
	$no_entries = '<p style="text-align:center;">[NO_ENTRIES]</p>';
	$no_entries = addslashes($no_entries);
	$query_dates = $database->query("SELECT `section_id`,`gbk_loop` FROM `".TABLE_PREFIX."mod_guestbook_settings` where `section_id` != 0 and `page_id` != 0");
	// add value to `no_entries`
	$database->query("UPDATE `".TABLE_PREFIX."mod_guestbook_settings` SET `no_entries`='$no_entries' WHERE `no_entries`='' AND `section_id`!=0");
	if($database->is_error()) echo 'Error: '.$database->get_error();
	// add field `ip_addr`
	$database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook` ADD `ip_addr` INT UNSIGNED NOT NULL DEFAULT '0'");
	if($database->is_error()) echo 'Error: '.$database->get_error();
}


// #######################################################################
// VERSION < 2.8.2
if(version_compare($module_version, '2.8.2', '<')) {
	$add_entry = '<form [GB_FORM]> [ASP_FIELDS]
  <table class="input_form" cellpadding="2" cellspacing="0" align="center" border="0">
  <tr>
    <td>[NAME_STR]:</td>
    <td><input class="input_name_field" [NAME_INPUT] /></td>
  </tr>
  <tr>
    <td>[EMAIL_STR]:</td>
    <td><input class="input_mail_field" [EMAIL_INPUT] /></td>
  </tr>
  <tr>
    <td>[WEBSITE_STR]:</td>
    <td><input class="input_website_field" [WEBSITE_INPUT] /></td>
  </tr>
  <tr class="smileys_row">
    <td class="input_smileys" colspan="2">[SMILEYS]</td>
  </tr>
  <tr>
    <td valign="top">[MESSAGE_STR]:</td>
    <td><textarea class="input_message_field" [MESSAGE_INPUT]>[MESSAGE]</textarea></td>
  </tr>	
  <tr>
    <td>[CAPTCHA_STR]</td>
    <td>[CAPTCHA]</td>
  </tr>  
  <tr>
    <td></td>
    <td>
      <input type="submit" value="[SUBMIT_STR]" />
      <input type="button" value="[RESET_STR]" onclick="[RESET_ONCLICK]" />
    </td>
  </tr>
</table>
</form>';
	$add_entry = addslashes($add_entry);
	// add [FLAG]
	$query_dates = $database->query("SELECT `gbk_loop`,`gbk_loop_b`,`section_id` FROM `".TABLE_PREFIX."mod_guestbook_settings` where `section_id` != 0 and `page_id` != 0");
	while($result = $query_dates->fetchRow()) {
		$section_id = $result['section_id'];
		$gbk_loop = $result['gbk_loop'];
		$gbk_loop_b = $result['gbk_loop_b'];
		$gbk_loop = preg_replace('#(\[NAME\])#','[NAME] [FLAG]',$gbk_loop);
		$gbk_loop_b = preg_replace('#(\[NAME\])#','[NAME] [FLAG]',$gbk_loop_b);
		$gbk_loop = addslashes($gbk_loop);
		$gbk_loop_b = addslashes($gbk_loop_b);
		$database->query("UPDATE `".TABLE_PREFIX."mod_guestbook_settings` SET `gbk_loop`='$gbk_loop',`gbk_loop_b`='$gbk_loop_b' WHERE `section_id`=$section_id");
		if($database->is_error()) echo 'Error: '.$database->get_error();
	}
	// add field add_entry to settings-table
	$database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook_settings` ADD `add_entry` TEXT NOT NULL");
	if($database->is_error()) echo 'Error: '.$database->get_error();
	// add value to `add_entry` into each guestbook
	$database->query("UPDATE `".TABLE_PREFIX."mod_guestbook_settings` SET `add_entry`='$add_entry' WHERE `add_entry`='' AND `section_id`!=0");
	if($database->is_error()) echo 'Error: '.$database->get_error();
	// add field store_server_vars to settings-table
	$database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook_settings` ADD `store_server_vars` TINYINT NOT NULL DEFAULT '0'");
	if($database->is_error()) echo 'Error: '.$database->get_error();
	// add field use_as_default to settings-table
	$database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook_settings` ADD `use_as_default` TINYINT NOT NULL DEFAULT '0'");
	if($database->is_error()) echo 'Error: '.$database->get_error();
	// add field server_vars to guestbook-table
	$database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook` ADD `server_vars` TEXT NOT NULL");
	if($database->is_error()) echo 'Error: '.$database->get_error();
	// add field textarea_style to settings-table
	$database->query("ALTER TABLE `".TABLE_PREFIX."mod_guestbook_settings` ADD `textarea_style` TINYINT NOT NULL DEFAULT '2'");
	if($database->is_error()) echo 'Error: '.$database->get_error();
}


