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

// Stop this file from being accessed directly
if(!defined('WB_URL')) exit(header('Location: ../../index.php'));

/* Note:
 * The target (jumping to the matching entry) will only work
 * if "Settings"->"Messages Per Page" is set != 0
 */

function guestbook_search($func_vars) {
	extract($func_vars, EXTR_PREFIX_ALL, 'func');

	// how many lines of excerpt we want to have at most
	$max_excerpt_num = $func_default_max_excerpt;
	$divider = ".";
	$result = false;

	// ordering of entries
	$table_settings = TABLE_PREFIX."mod_guestbook_settings";
	$query_settings = $func_database->query("SELECT `ordering` FROM `$table_settings` WHERE `section_id`='$func_section_id'");
	$settings = $query_settings->fetchRow();
	$ordering = $settings['ordering']=='0'?'ASC':'DESC';

	// fetch all guestbook-entries from this section
	$table = TABLE_PREFIX."mod_guestbook";
	$query = $func_database->query("
		SELECT `name`, `email`, `homepage`, `message`, `comment`, `posted_when`, `position`
		FROM `$table`
		WHERE `section_id`='$func_section_id' AND `approved`=1
		ORDER BY `position` $ordering
	");
	// now call print_excerpt() for every single item
	if($query->numRows() > 0) {
		$i=0;
		while($res = $query->fetchRow()) {
			$mod_vars = array(
				'page_link' => $func_page_link,
				'page_link_target' => "&p=".$i++, // jump to guestbook-entry
				'page_title' => $func_page_title,
				'page_description' => $func_page_description,
				'page_modified_when' => $res['posted_when'],
				'page_modified_by' => "", // there's no posted_by for guestbook entries
				'text' => $res['name'].$divider.$res['email'].$divider.$res['homepage'].$divider.$res['message'].$divider.$res['comment'].$divider,
				'max_excerpt_num' => $max_excerpt_num
			);
			if(print_excerpt2($mod_vars, $func_vars)) {
				$result = true;
			}
		}
	}
	return $result;
}

