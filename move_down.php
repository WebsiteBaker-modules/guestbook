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

// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

// Get id
if(!isset($_GET['entry_id']) OR !is_numeric($_GET['entry_id'])) {
	header("Location: index.php");
} else {
	$id = $_GET['entry_id'];
	$id_field = 'id';
	$cf_field = 'section_id';
	$table = TABLE_PREFIX.'mod_guestbook';
	$url = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
}

// Include the ordering class
require_once(WB_PATH.'/framework/class.order.php');

// Create new order object an reorder
$order = new order($table, 'position', $id_field, $cf_field);
if($order->move_down($id)) {
	$admin->print_success($TEXT['SUCCESS'], $url);
} else {
	$admin->print_error($TEXT['ERROR'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

