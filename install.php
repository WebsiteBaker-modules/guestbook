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
if(!defined('WB_PATH')) die(header('Location: ../index.php'));  

// STEP 1: 			Create Tables

// STEP 1.1.:		Create actual guestbook table
$table = TABLE_PREFIX.'mod_guestbook';
$database->query("DROP TABLE IF EXISTS `$table`");
$mod_guestbook = "CREATE TABLE `$table` (
	`section_id`  INT NOT NULL DEFAULT '0' ,
	`page_id`     INT NOT NULL DEFAULT '0' ,
	`id`          INT NOT NULL AUTO_INCREMENT ,
	`name`        VARCHAR(50) NOT NULL ,
	`email`       VARCHAR(50) NOT NULL ,
	`homepage`    VARCHAR(50) NOT NULL ,
	`message`     TEXT NOT NULL ,
	`comment`     TEXT NOT NULL ,
	`posted_when` INT NOT NULL DEFAULT '0' ,
	`position`    INT NOT NULL DEFAULT '0' ,
	`approved`    TINYINT NOT NULL DEFAULT '0' ,
	`ip_addr`     INT UNSIGNED NOT NULL DEFAULT '0' ,
	`server_vars` TEXT NOT NULL ,
	PRIMARY KEY (`id`)
)";
$database->query($mod_guestbook);
if($database->is_error()) echo $database->get_error().'<br />';

// STEP 1.2.:		Create the guestbook settings table
$table = TABLE_PREFIX.'mod_guestbook_settings';
$database->query("DROP TABLE IF EXISTS `$table`");
$mod_guestbook_settings = "CREATE TABLE `$table` (
	`section_id`        INT NOT NULL DEFAULT '0' ,
	`page_id`           INT NOT NULL DEFAULT '0' ,
	`header`            TEXT NOT NULL ,
	`gbk_loop`          TEXT NOT NULL ,
	`gbk_loop_b`        TEXT NOT NULL ,
	`footer`            TEXT NOT NULL ,
	`no_entries`        TEXT NOT NULL ,
	`add_entry`         TEXT NOT NULL ,
	`image_links`       TINYINT NOT NULL DEFAULT '0' ,
	`entries_per_page`  INT NOT NULL DEFAULT '0' COMMENT '0:no limit' ,
	`email_required`    TINYINT NOT NULL DEFAULT '0' ,
	`show_unused_fields` TINYINT NOT NULL DEFAULT '0' ,
	`ordering`          TINYINT NOT NULL DEFAULT '0' COMMENT '0:ASCENDING, 1:DESCENDING' ,
	`use_captcha`       TINYINT NOT NULL DEFAULT '0' ,
	`auto_approve`      TINYINT NOT NULL DEFAULT '0' ,
	`admin_email`       TEXT NOT NULL ,
	`show_smiley`       TINYINT NOT NULL DEFAULT '0' ,
	`store_server_vars` TINYINT NOT NULL DEFAULT '0' ,
	`use_as_default`    TINYINT NOT NULL DEFAULT '0' ,
	`textarea_style`    TINYINT NOT NULL DEFAULT '2' COMMENT '0:normal,1:autogrow,2:codepress',
	PRIMARY KEY (`section_id`)
)";
$database->query($mod_guestbook_settings);
if($database->is_error()) echo $database->get_error().'<br />';

// STEP 2:		Insert info into the search table

// STEP 2.1:	Module query info
$field_info = array();
$field_info['page_id'] = 'page_id';
$field_info['title'] = 'page_title';
$field_info['link'] = 'link';
$field_info = serialize($field_info);
$database->query("INSERT INTO `".TABLE_PREFIX."search` (`name`, `value`, `extra`) VALUES ('module', 'guestbook', '$field_info')");
// STEP 2.2:	Query start
$query_start_code = "SELECT [TP]pages.page_id, [TP]pages.page_title, [TP]pages.link FROM [TP]mod_guestbook, [TP]pages WHERE ";
$database->query("INSERT INTO `".TABLE_PREFIX."search` (`name`, `value`, `extra`) VALUES ('query_start', '$query_start_code', 'guestbook')");
// STEP 2.3.:	Query body
$query_body_code = "
[TP]pages.page_id = [TP]mod_guestbook.page_id AND [TP]mod_guestbook.name [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
[TP]pages.page_id = [TP]mod_guestbook.page_id AND [TP]mod_guestbook.email [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
[TP]pages.page_id = [TP]mod_guestbook.page_id AND [TP]mod_guestbook.homepage [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
[TP]pages.page_id = [TP]mod_guestbook.page_id AND [TP]mod_guestbook.message [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\'
";	
$database->query("INSERT INTO `".TABLE_PREFIX."search` (`name`, `value`, `extra`) VALUES ('query_body', '$query_body_code', 'guestbook')");
// STEP 2.4.:	Query end
$query_end_code = "";	
$database->query("INSERT INTO `".TABLE_PREFIX."search` (`name`, `value`, `extra`) VALUES ('query_end', '$query_end_code', 'guestbook')");

// STEP 3:		Add empty rows [ Insert blank rows (there needs to be at least on row for the search to work) ]
$database->query("INSERT INTO `".TABLE_PREFIX."mod_guestbook` (`section_id`, `page_id`) VALUES ('0', '0')");
$database->query("INSERT INTO `".TABLE_PREFIX."mod_guestbook_settings` (`section_id`, `page_id`) VALUES ('0', '0')");

