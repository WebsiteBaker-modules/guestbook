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

// Include config file
require('../../config.php');

// Validation:		Check if details are correct. If not navigate to main.
if(isset($_GET['sid']) && is_numeric($_GET['sid']) && isset($_GET['pid']) && is_numeric($_GET['pid'])) {
	$section_id = $_GET['sid'];
	$page_id    = $_GET['pid'];
	define('SECTION_ID', $section_id);
} else {
	exit(header('Location: '.WB_URL.PAGES_DIRECTORY));
}

// Include database class
require_once(WB_PATH.'/framework/class.database.php');

//ban block check
if (file_exists(WB_PATH.'/modules/ban/banblock.php')){
	include WB_PATH.'/modules/ban/banblock.php';
}

// don't allow entry if ASP enabled and user doesn't comes from view.php
if(defined('ENABLED_ASP') && ENABLED_ASP) {
	if(!isset($_GET['add']) || !is_numeric($_GET['add'])
	  || !isset($_SESSION['comes_from_view_gb']) || $_SESSION['comes_from_view_gb']!=$_GET['add']
	) {
		exit(header("Location: ".WB_URL.PAGES_DIRECTORY));
	}
}

// STEP 1:			Query for page id
$query_page = $database->query("
	SELECT `parent`, `page_title`, `menu_title`, `keywords`, `description`, `visibility`
	FROM `".TABLE_PREFIX."pages` p INNER JOIN `".TABLE_PREFIX."sections` s ON (p.`page_id`=s.`page_id`)
	WHERE `p`.`page_id` = '$page_id' AND `section_id` = '$section_id'
");
if($query_page->numRows() == 0) {
	exit(header('Location: '.WB_URL.PAGES_DIRECTORY));
} else {
	$page = $query_page->fetchRow();
	// Required page details
	define('PAGE_CONTENT', WB_PATH.'/modules/guestbook/gstbk_page.php');
	// Include index (wrapper) file
	require(WB_PATH.'/index.php');
}

