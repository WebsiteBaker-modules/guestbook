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
require(WB_PATH.'/modules/admin.php');		// Include WB admin wrapper script

$table_settings = TABLE_PREFIX.'mod_guestbook_settings';

// Initialize:	Tell script to update when this page was last updated
$update_when_modified = true;
$friendly = array('?php'); // we need to convert < to &lt; to display in textarea, but not vica verse
$raw = array('');

// STEP 1:		Get Settings from POST Var and validate the entries
if (isset($_POST['email_required']) AND is_numeric($_POST['email_required'])) {
    $email_required = $_POST['email_required'];
} else {
    $email_required = '0';
}
if (isset($_POST['ordering']) AND is_numeric($_POST['ordering'])) {
    $ordering = $_POST['ordering'];
} else {
    $ordering = '0';
}
if (isset($_POST['show_unused_fields']) AND is_numeric($_POST['show_unused_fields'])) {
    $show_unused_fields = $_POST['show_unused_fields'];
} else {
    $show_unused_fields = '0';
}
if (isset($_POST['image_links']) AND is_numeric($_POST['image_links'])) {
    $image_links = $_POST['image_links'];
} else {
    $image_links = '0';
}

if (isset($_POST['auto_approve']) AND is_numeric($_POST['auto_approve'])) {
    $auto_approve = $_POST['auto_approve'];
} else {
    $auto_approve = '0';
}

if (isset($_POST['setting_email'])) {
    $admin_email = $_POST['setting_email'];
} else {
    $admin_email = '';
}

if (isset($_POST['show_smiley'])) {
    $show_smiley = $_POST['show_smiley'];
} else {
    $show_smiley = '0';
}

if (isset($_POST['store_server_vars']) AND is_numeric($_POST['store_server_vars'])) {
    $store_server_vars = $_POST['store_server_vars'];
} else {
    $store_server_vars = '0';
}

if (isset($_POST['entries_per_page']) AND is_numeric($_POST['entries_per_page'])) {
    $entries_per_page = $_POST['entries_per_page'];
} else {
    $entries_per_page = '0';
}

if (isset($_POST['textarea_style']) AND is_numeric($_POST['textarea_style'])) {
    $textarea_style = $_POST['textarea_style'];
} else {
    $textarea_style = '2';
}

if(extension_loaded('gd') AND function_exists('imageCreateFromJpeg')) {
	$use_captcha = $_POST['use_captcha'];
} else {
	$use_captcha = false;
}

$header = $admin->add_slashes(str_replace($friendly, $raw, $_POST['header']));
$gbk_loop = $admin->add_slashes(str_replace($friendly, $raw, $_POST['gbk_loop']));
$gbk_loop_b = $admin->add_slashes(str_replace($friendly, $raw, $_POST['gbk_loop_b']));
$footer = $admin->add_slashes(str_replace($friendly, $raw, $_POST['footer']));
$no_entries = $admin->add_slashes(str_replace($friendly, $raw, $_POST['no_entries']));
$add_entry = $admin->add_slashes(str_replace($friendly, $raw, $_POST['add_entry']));

// default-settings?
if (isset($_POST['use_as_default']) AND is_numeric($_POST['use_as_default'])) {
    $use_as_default = $_POST['use_as_default'];
} else {
    $use_as_default = '0';
}
if($use_as_default) {
	// unset old default
	$database->query("UPDATE `$table_settings` SET `use_as_default`=0 WHERE `use_as_default`=1");
}

// STEP 2:		Write Settings to Database (LINE ADDED BY PHIL EMERSON FOR APPROVAL MODIFICATION: DECEMBER 2006)
$database->query("
	UPDATE `$table_settings` 
	SET `email_required` = '$email_required', 
	`ordering` = '$ordering', 
	`entries_per_page` = '$entries_per_page', 
	`image_links` = '$image_links',
	`show_unused_fields` = '$show_unused_fields',
	`use_captcha` = '$use_captcha',
	`header` = '$header',
	`gbk_loop` = '$gbk_loop',
	`gbk_loop_b` = '$gbk_loop_b',
	`footer` = '$footer',
	`no_entries` = '$no_entries',
	`add_entry` = '$add_entry',
	`auto_approve` = '$auto_approve',
	`admin_email` = '$admin_email',
	`show_smiley` = '$show_smiley',
	`store_server_vars` = '$store_server_vars',
	`use_as_default` = '$use_as_default',
	`textarea_style` = '$textarea_style'
	WHERE `section_id` = '$section_id'"
);

// STEP 3:		Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'&section_id='.$section_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'&section_id='.$section_id);
}

// Print admin footer
$admin->print_footer();

