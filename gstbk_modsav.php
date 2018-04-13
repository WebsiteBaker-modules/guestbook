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

// Get id
if(!isset($_POST['entry_id']) OR !is_numeric($_POST['entry_id'])) {
	exit(header("Location: ".ADMIN_URL."/pages/index.php"));
} else {
	$entry_id = $_POST['entry_id'];
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

// Validate all fields
if($admin->get_post('message') == '' AND $admin->get_post('date') == '') {
	$admin->print_error($MESSAGE['GENERIC']['FILL_IN_ALL'], WB_URL.'/modules/guestbook/gstbk_modify.php?page_id='.$page_id.'&section_id='.$section_id.'&entry_id='.$entry_id);
} else {
	$page_id = $admin->get_post_escaped('page_id');
	$section_id = $admin->get_post_escaped('section_id');
	$position = $admin->get_post_escaped('position');
	$name = $admin->get_post_escaped('u_name');
	$date = $admin->get_post_escaped('date');
	$email = $admin->get_post_escaped('email');
	$homepage = $admin->get_post_escaped('homepage');
	$message = $admin->get_post_escaped('message');
	$comment = $admin->get_post_escaped('comment');
}

// Update row
$database->query("UPDATE `".TABLE_PREFIX."mod_guestbook` SET `name` = '$name', `email` = '$email', `homepage` = '$homepage', `posted_when` = '$date', `message` = '$message', `comment` = '$comment' WHERE `id` = '$entry_id'");

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
  $admin->print_error($database->get_error(), ADMIN_URL.'/pages/pages/modify.php?page_id='.$page_id.'&section_id='.$section_id);
} else {
  $admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'&section_id='.$section_id);
}

// Print admin footer
$admin->print_footer();

