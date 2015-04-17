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

// STEP 1:	Initialize
require('../../config.php');
require(WB_PATH.'/modules/admin.php');					// Include WB admin wrapper script

// include core functions of WB 2.7 to edit the optional module CSS files (frontend.css, backend.css)
@include_once(WB_PATH .'/framework/module.functions.php');

// check if module language file exists for the language set by the user (e.g. DE, EN)
if(file_exists(WB_PATH .'/modules/guestbook/languages/'.LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/guestbook/languages/'.LANGUAGE .'.php');
} else {
	require_once(WB_PATH .'/modules/guestbook/languages/EN.php');
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


$raw = array('&', '<', '>');                  // Set raw html <'s and >'s to be
$friendly = array('&amp;', '&lt;', '&gt;');   // replace by friendly html code

// STEP 2:	Get actual settings from database
$query_settings = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_guestbook_settings` WHERE `section_id` = '$section_id'");
$settings = $query_settings->fetchRow();

// STEP 3:	Display the settings for modification.

// include the button to edit the optional module CSS files
// Note: CSS styles for the button are defined in backend.css (div class="mod_moduledirectory_edit_css")
// Place this call outside of any <form></form> construct!!!
if(function_exists('edit_module_css')) {
	ob_start();
	edit_module_css('guestbook');
	$edit_css = ob_get_contents();
	ob_end_clean();
}

// check out what to call: autogrow or editarea
$call_js = '';
$format_field_0_checked = $format_field_1_checked = $format_field_2_checked = '';
if($settings['textarea_style']==0) {
	$format_field_0_checked = 'checked="checked"';
} elseif($settings['textarea_style']==1) { // autogrow
	$format_field_1_checked = 'checked="checked"';
	echo '<script type="text/javascript">$(document).ready(function(){$("textarea").growfield();});</script>';
} elseif($settings['textarea_style']==2) { // editarea
	$format_field_2_checked = 'checked="checked"';
		$call_js = '
<script language="javascript" type="text/javascript">editAreaLoader.init({ id : "no_entries", syntax: "html", start_highlight: true });</script>
<script language="javascript" type="text/javascript">editAreaLoader.init({ id : "footer", syntax: "html", start_highlight: true });</script>
<script language="javascript" type="text/javascript">editAreaLoader.init({ id : "add_entry", syntax: "html", start_highlight: true });</script>
<script language="javascript" type="text/javascript">editAreaLoader.init({ id : "gbk_loop_b", syntax: "html", start_highlight: true });</script>
<script language="javascript" type="text/javascript">editAreaLoader.init({ id : "gbk_loop", syntax: "html", start_highlight: true });</script>
<script language="javascript" type="text/javascript">editAreaLoader.init({ id : "header", syntax: "html", start_highlight: true });</script>
';
	if(file_exists(WB_PATH .'/include/editarea/edit_area_full.js')) {
		echo '<script language="javascript" type="text/javascript" src="'.WB_URL.'/include/editarea/edit_area_full.js"></script>';
	} elseif(file_exists(WB_PATH .'/modules/editarea/edit_area/edit_area_full.js')) {
		echo '<script language="javascript" type="text/javascript" src="'.WB_URL.'/modules/editarea/edit_area/edit_area_full.js"></script>';
	} else {
		$call_js = '';
	}
}

// Template
$t = new Template(dirname(__FILE__).'/templates', 'remove');
$t->halt_on_error = 'no';
$t->set_file('modify_settings', 'modify_settings.htt');
// clear the comment-block, if present
 $t->set_block('modify_settings', 'CommentDoc'); $t->clear_var('CommentDoc');
$t->set_var(array(
	'HIDDEN' => 'style="display:none !important;"',
	'SETTINGS_STRING' => $MOD_GUESTBOOK['SETTINGS'],
	'EDIT_CSS' => $edit_css,
	'SETTINGS_URL' => WB_URL.'/modules/guestbook/save_settings.php',
	'SECTION_ID_VALUE' => $section_id,
	'PAGE_ID_VALUE' => $page_id,
	'MAIN_STRING' => $MOD_GUESTBOOK['MAIN_SETTINGS'],
	'MSG_PER_PAGE_STRING' => $MOD_GUESTBOOK['MESSAGES_PER_PAGE'],
	'MSG_PER_PAGE' => $settings['entries_per_page'],
	'UNLIMITED_STRING' => '0 = '.$TEXT['UNLIMITED'],
	'EMAIL_REQ_STRING' => $MOD_GUESTBOOK['EMAIL_REQUIRED'],
	'EMAIL_REQ_CHECKED' => ($settings['email_required']==1?'checked="checked"':''),
	'SHOW_UNUSED_STRING' => $MOD_GUESTBOOK['SHOW_UNUSED_FIELDS'],
	'SHOW_UNUSED_CHECKED' => ($settings['show_unused_fields']==1?'checked="checked"':''),
	'SHOW_IMAGE_STRING' => $MOD_GUESTBOOK['SHOW_IMAGE_LINKS'],
	'SHOW_IMAGE_CHECKED' => ($settings['image_links']==1?'checked="checked"':''),
	'SHOW_SMILEYS_STRING' => $MOD_GUESTBOOK['SHOW_SMILEYS'],
	'SHOW_SMILEYS_CHECKED' => ($settings['show_smiley']==1?'checked="checked"':''),
	'AUTO_APPROVE_STRING' => $MOD_GUESTBOOK['AUTO_APPROVE'],
	'AUTO_APPROVE_CHECKED' => ($settings['auto_approve']==1?'checked="checked"':''),
	'CAPTCHA_ENABLED_STRING' => $MOD_GUESTBOOK['VERIFICATION_ENABLE'],
	'CAPTCHA_ON_CHECKED' => ($settings['use_captcha']==1?'checked="checked"':''),
	'CAPTCHA_OFF_CHECKED' => ($settings['use_captcha']==0?'checked="checked"':''),
	'ENABLED_STRING' => $TEXT['ENABLED'],
	'DISABLED_STRING' => $TEXT['DISABLED'],
	'ORDERING_STRING' => $MOD_GUESTBOOK['ORDERING'],
	'ASC_SELECTED' => ($settings['ordering']==0?'selected="selected"':''),
	'DESC_SELECTED' => ($settings['ordering']!=0?'selected="selected"':''),
	'ASCENDING_STRING' => $MOD_GUESTBOOK['ASCENDING'],
	'DESCENDING_STRING' => $MOD_GUESTBOOK['DESCENDING'],
	'ADMIN_EMAIL_STRING' => $MOD_GUESTBOOK['ADMINEMAIL'],
	'ADMIN_EMAIL' => $settings['admin_email'],
	'MAILNOTIFY_STRING' => $MOD_GUESTBOOK['MAILNOTIFIKATION'],
	'SERVER_VARS_CHECKED' => ($settings['store_server_vars']==1?'checked="checked"':''),
	'SERVER_VARS_STRING' => $MOD_GUESTBOOK['SERVER_VARS'],
	'DEFAULT_CHECKED' => ($settings['use_as_default']==1?'checked="checked"':''),
	'DEFAULT_STRING' => $MOD_GUESTBOOK['USE_AS_DEFAULT'],
	'DEFAULT_TEXT' => $MOD_GUESTBOOK['USE_AS_DEFAULT_TEXT'],
	'FORMAT_FIELD_STRING' => $MOD_GUESTBOOK['FORMAT_FIELD_STRING'],
	'FORMAT_FIELD_0_CHECKED' => $format_field_0_checked,
	'FORMAT_FIELD_1_CHECKED' => $format_field_1_checked,
	'FORMAT_FIELD_2_CHECKED' => $format_field_2_checked,
	'LAYOUT_STRING' => $MOD_GUESTBOOK['LAYOUT_SETTINGS'],
	'HEADER_STRING' => $MOD_GUESTBOOK['HEADER'],
	'HEADER' => str_replace($raw, $friendly, $settings['header']),
	'LOOP_STRING' => $MOD_GUESTBOOK['LOOP'],
	'LOOP' => str_replace($raw, $friendly, $settings['gbk_loop']),
	'LOOPB_STRING' => $MOD_GUESTBOOK['LOOP_B'],
	'LOOPB' => str_replace($raw, $friendly, $settings['gbk_loop_b']),
	'FOOTER_STRING' => $MOD_GUESTBOOK['FOOTER'],
	'FOOTER' => str_replace($raw, $friendly, $settings['footer']),
	'NOENTRIES_STRING' => $MOD_GUESTBOOK['NO_MESSAGE'],
	'NOENTRIES' => $settings['no_entries'],
	'ADDENTRY_STRING' => $MOD_GUESTBOOK['SIGN_GSTBK'],
	'ADDENTRY' => str_replace($raw, $friendly, $settings['add_entry']),
	'SAVE_STRING' => $TEXT['SAVE'],
	'CANCEL_STRING' => $TEXT['CANCEL'],
	'CANCEL_ONCLICK' => 'javascript: window.location = \''.ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'\';'
));
$t->pparse('Output', 'modify_settings');

echo $call_js;

// Print admin footer
$admin->print_footer();

