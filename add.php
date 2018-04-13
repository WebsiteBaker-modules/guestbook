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
if(!defined('WB_PATH')) die(header('Location: index.php'));  

// check default-settings
$table_settings = TABLE_PREFIX.'mod_guestbook_settings';
$use_default = FALSE;

$query_settings = $database->query("SELECT * FROM `$table_settings` WHERE `use_as_default`=1");
if($query_settings && $settings = $query_settings->fetchRow()) {

	// Insert default-settings into database
	$header = addslashes($settings['header']);
	$gbk_loop = addslashes($settings['gbk_loop']);
	$gbk_loop_b = addslashes($settings['gbk_loop_b']);
	$footer = addslashes($settings['footer']);
	$no_entries = addslashes($settings['no_entries']);
	$add_entry = addslashes($settings['add_entry']);
	$entries_per_page = $settings['entries_per_page'];
	$use_captcha = $settings['use_captcha'];
	$image_links = $settings['image_links'];
	$email_required = $settings['email_required'];
	$ordering = $settings['ordering'];
	$show_unused_fields = $settings['show_unused_fields'];
	$auto_approve = $settings['auto_approve'];
	$admin_email = $settings ['admin_email'];
	$show_smiley = $settings['show_smiley'];
	$store_server_vars = $settings['store_server_vars'];
	$use_as_default = 0;

} else {

	$header = '<div align="center">[ADD_ENTRY]<br /><br /></div>';

	$gbk_loop = '<table class="gbentry">
 <tr>
  <td class="gbtitle">[NAME] [FLAG] [EMAIL] [HOMEPAGE]</td>
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
  <td class="gbtitle">[NAME] [FLAG] [EMAIL] [HOMEPAGE]</td>
  <td class="gbtitle" align="right">[DATE] [TIME]</td>
 </tr>
 <tr>
  <td colspan="2" class="gbcontent">[MESSAGE]
    <div class="gbcomment">[COMMENT]</div>
  </td>
 </tr>
</table>';

	$footer = '<div style="width:33%;float:left;text-align:left;">[PREVIOUS_PAGE_LINK]</div>
<div style="width:34%;float:left;text-align:center;">[OF]</div>
<div style="width:33%;float:left;text-align:right;">[NEXT_PAGE_LINK]</div>
<div style="clear:both"></div>';

	$no_entries = '<p style="text-align:center;">[NO_ENTRIES]</p>';

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

	// Insert values into database
	$header = addslashes($header);
	$gbk_loop = addslashes($gbk_loop);
	$gbk_loop_b = addslashes($gbk_loop_b);
	$footer = addslashes($footer);
	$no_entries = addslashes($no_entries);
	$add_entry = addslashes($add_entry);
	$entries_per_page = 10;
	$use_captcha = 1;
	$image_links = 1;
	$email_required = 0;
	$ordering = 1;
	$show_unused_fields = 1;
	$auto_approve = 1;
	$admin_email = '';
	$show_smiley = 1;
	$store_server_vars = 0;
	$use_as_default = 0;

}

$database->query("
	INSERT INTO `".TABLE_PREFIX."mod_guestbook_settings`
	(`section_id` , `page_id`, `header`, `gbk_loop`, `gbk_loop_b`, `footer`, `no_entries`, `add_entry`, `image_links`, `entries_per_page`, `email_required`, `ordering`, `show_unused_fields`, `use_captcha`, `auto_approve`, `admin_email`, `show_smiley`, `store_server_vars`, `use_as_default`)
	VALUES
	('$section_id','$page_id','$header','$gbk_loop','$gbk_loop_b','$footer','$no_entries','$add_entry','$image_links','$entries_per_page','$email_required','$ordering','$show_unused_fields','$use_captcha','$auto_approve','$admin_email','$show_smiley','$store_server_vars','$use_as_default')
");

