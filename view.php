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

// Validation:		Must include code to stop this file being access directly
if(!defined('WB_PATH')) { die(header('Location: ../../index.php')); }

// check if module language file exists for the language set by the user (e.g. DE, EN)
if(file_exists(WB_PATH.'/modules/guestbook/languages/'.LANGUAGE.'.php')) {
	require_once(WB_PATH.'/modules/guestbook/languages/'.LANGUAGE.'.php');
} else {
	require_once(WB_PATH.'/modules/guestbook/languages/EN.php');
}

// check if frontend.css file needs to be included into the <body></body> of view.php
if((!function_exists('register_frontend_modfiles') || !defined('MOD_FRONTEND_CSS_REGISTERED')) &&  file_exists(WB_PATH .'/modules/guestbook/frontend.css')) {
	echo '<style type="text/css">';
	include(WB_PATH .'/modules/guestbook/frontend.css');
	echo "\n</style>\n";
} 

// load geoip-database, if available
if(file_exists(WB_PATH.'/modules/geoip/geoip_functions.php')) {
	require_once(WB_PATH.'/modules/geoip/geoip_functions.php');
	$gi = geoip_open_db(); // defines constant GEOIP_DATABASE_LOADED on success
}

//overwrite php.ini on Apache servers for valid SESSION ID Separator
if(function_exists('ini_set')) {
	ini_set('arg_separator.output', '&amp;');
}

// STEP 1: get the settings for this section from the database
$query_settings = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_guestbook_settings` WHERE `section_id` = '$section_id'");
$settings = $query_settings->fetchRow();
$page_id = $settings['page_id'];
$entries_per_page = $settings['entries_per_page'];
$email_required = $settings['email_required'];
$show_unused_fields = $settings['show_unused_fields'];
$show_smiley = $settings['show_smiley'];
$show_image_links = $settings['image_links'];
$entry_loop = $settings['gbk_loop'];
$entry_loop_b = $settings['gbk_loop_b'];
if($entry_loop_b=='') $entry_loop_b = $entry_loop;
$guestbook_header = $settings['header'];
$guestbook_footer = $settings['footer'];
$guestbook_no_entries = $settings['no_entries'];
$WB_PATH = WB_PATH;
$WB_URL = WB_URL;

function smile_replace ($tekst) {
	static $smileys_tmp = FALSE;
	if($smileys_tmp===FALSE) {
		require_once(WB_PATH.'/modules/guestbook/smileys.php');
		foreach($smileys as $sm_code=>$sm_url) {
			$smileys_tmp[$sm_code] = "<img class=\"smileys\" src=\"$sm_url\" alt=\"$sm_code\"/>";
		}
	}
	$tekst = strtr($tekst, $smileys_tmp);
	return($tekst);
}

// STEP 1.1.		Change some values based upon the settings
if ($settings['ordering'] == '1') {
    $ordering = 'DESC';
} else {
    $ordering = 'ASC';
}
if ($email_required == '1') {
    $star_email = '*';
} else {
    $star_email = '';
}

// STEP 2:			Start the actual output for the Guestbook page
$sign_gstbk = $MOD_GUESTBOOK['SIGN_GSTBK'];

// STEP 2.2.		Define previous next links when a maximum of pages is set

// STEP 2.2.1.		Check if there is a start point defined
if(isset($_GET['p']) AND is_numeric($_GET['p']) AND $_GET['p'] >= 0) {
	$position = $_GET['p'];
} else {
	$position = 0;
}

// STEP 2.2.2.		Get total number of posts
$query_total_num = $database->query("SELECT `id` FROM `".TABLE_PREFIX."mod_guestbook` WHERE `section_id` = '$section_id' AND `approved` = '1'");
if ($query_total_num){
	$total_num = $query_total_num->numRows();
} else {
	$total_num=0;
}

// STEP 2.2.3.		Work-out if we need to add limit code to sql
if($entries_per_page != 0) {
	$limit_sql = " LIMIT $position,$entries_per_page";
} else {
	$limit_sql = "";
}

// STEP 2.3.		Display the guestbook entries
$query_entries = $database->query("SELECT *,`ip_addr` as `user_ip` FROM `".TABLE_PREFIX."mod_guestbook` WHERE `page_id` = '$page_id' AND `section_id` = '$section_id' AND `approved` = '1' ORDER BY position $ordering".$limit_sql);
if($query_entries){
	$num_entries = $query_entries->numRows();
} else {
	$num_entries=0;
}

//overwrite php.ini for valid SESSION ID Separator
ini_set( 'arg_separator.output' , '&amp;' );

// STEP 2.3.1.		Create previous and next links
if($entries_per_page != 0) {
	if($position > 0) {
		$pl_prepend = '<a href="?p='.($position-$entries_per_page).'">&lt;&lt;&nbsp;';
		$pl_append = '</a>';
		$previous_link = $pl_prepend.$TEXT['PREVIOUS'].$pl_append;
		$previous_page_link = $pl_prepend.$TEXT['PREVIOUS_PAGE'].$pl_append;
	} else {
		$previous_link = '&nbsp;';
		$previous_page_link = '&nbsp;';
	}
	if($position+$entries_per_page >= $total_num) {
		$next_link = '&nbsp;';
		$next_page_link = '&nbsp;';
	} else {
		$nl_prepend = '<a href="?p='.($position+$entries_per_page).'"> ';
		$nl_append = '&nbsp;&gt;&gt;</a>';
		$next_link = $nl_prepend.$TEXT['NEXT'].$nl_append;
		$next_page_link = $nl_prepend.$TEXT['NEXT_PAGE'].$nl_append;
	}
	if($position+$entries_per_page > $total_num) {
		$num_of = $position+$num_entries;
	} else {
		$num_of = $position+$entries_per_page;
	}
	$out_of = ($position+1).'-'.$num_of.' '.strtolower($TEXT['OUT_OF']).' '.$total_num;
	$of = ($position+1).'-'.$num_of.' '.strtolower($TEXT['OF']).' '.$total_num;
	$display_previous_next_links = '';
} else {
	$display_previous_next_links = 'none';
}

// STEP 2.3.2.		Set 'add entry' link
if(defined('ENABLED_ASP') && ENABLED_ASP) {
	mt_srand((double)microtime()*100000);
	$cc = mt_rand(0,99);
	$_SESSION['comes_from_view_gb'] = $cc;
	$_SESSION['comes_from_view_gb_time'] = time();
	$add_entry_link = "<a href=\"$WB_URL/modules/guestbook/gstbk_add.php?sid=$section_id&amp;pid=$page_id&amp;add=$cc\">$sign_gstbk</a>";
} else {
	$add_entry_link = "<a href=\"$WB_URL/modules/guestbook/gstbk_add.php?sid=$section_id&amp;pid=$page_id\">$sign_gstbk</a>";
}

// STEP 2.3.3.		Print header
$hvars = array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]', '[ADD_ENTRY]');
if($display_previous_next_links == 'none') {
	$hvalues = array('','','','','','', $display_previous_next_links, $add_entry_link);
} else {
	$hvalues = array($next_page_link, $next_link, $previous_page_link, $previous_link, $out_of, $of, $display_previous_next_links, $add_entry_link);
}
echo str_replace($hvars, $hvalues, $guestbook_header);

// STEP 2.3.4.		Display the guestbook entries (via a loop)
if ($num_entries > 0) {
	$row = 0;
	while ($entry = $query_entries->fetchRow()) {
		// Cleaning variables
		$entry_name = '';
		$entry_email = '';
		$entry_www = '';
		$entry_txt = '';
		$entry_date = '';
		$entry_time = '';   
		$entry_comment = '';
		$entry_flag = '';
		// A:	Get the enty date and time
		$entry_date = gmdate(DATE_FORMAT, $entry['posted_when']+TIMEZONE);
		$entry_time = gmdate(TIME_FORMAT, $entry['posted_when']+TIMEZONE);
				
		// B:	Get the rest of the entries
		
		// B.1.	Modify the output for the 'name'
		$entry_name = $entry['name'];

		// B.2.	Modify the output for 'e-mail' (image or text, 1 == True)
		if ($show_image_links == '1' AND !$entry["email"] == '') {
			$img_email = '<a href="mailto:'.$entry['email'].'">'
			.'<img class="button" src="'.$WB_URL.'/modules/guestbook/images/mail.gif" width="14" height="17" border="0" alt="'.$entry['email'].'"'
			.' title="'.$entry['email'].'" /></a>';
			$entry_email = $img_email;
		} elseif(!$entry["email"] == '') {
			$entry_email = '<br /><a href="mailto:'.$entry['email'].'">'.$entry['email'].'</a>';
		}
		
		// B.3. Modify the output for 'homepage' (image or text, 1 == True)
		if ($show_image_links == '1' AND !$entry["homepage"] == '') {
			$img_www = '<a href="'.$entry['homepage'].'" target="_blank">'
			.'<img class="button" src="'.$WB_URL.'/modules/guestbook/images/home.gif" width="14" height="17" border="0" alt="'.$entry['homepage'].'"'
			.' title="'.$entry['homepage'].'" /></a>';
			$entry_www = $img_www;
		} elseif(!$entry["homepage"] == '') {
			$entry_www = '<br /><a href="'.$entry['homepage'].'">'.$entry['homepage'].'</a>';
		}

		// display flag if available
		if(defined('GEOIP_DATABASE_LOADED')) {
			$entry_flag = geoip_flag_html($gi, $entry['user_ip']);
		}

		// B.4.	Modify the message output.
		$entry_txt = $entry['message'];
		$entry_txt = nl2br($entry_txt);
		
		if ($entry['comment']) {
			$entry_comment = $entry['comment'];
			$entry_comment = nl2br($entry_comment);
		}
		
		//B.5.	Check if smileys are activated
		if ($show_smiley == '1') {
			$entry_txt = smile_replace($entry_txt);
			$entry_comment = smile_replace($entry_comment);
		}
		
		// C:	Replace vars with values
		$vars = array( '[NAME]', '[EMAIL]', '[HOMEPAGE]', '[FLAG]', '[MESSAGE]', '[COMMENT]', '[DATE]', '[TIME]' );
		$values = array( $entry_name, $entry_email, $entry_www, $entry_flag, $entry_txt, $entry_comment, $entry_date, $entry_time);
		// cycle row colors
		if($row==0) {
			$entry_loop_tmp = $entry_loop;
			$row = 1;
		} else {
			$entry_loop_tmp = $entry_loop_b;
			$row = 0;
		}
		// change $entry_loop_tmp in case there is no comment to display, that is add style="display:none !important;" to comment
		if(!$entry_comment) {
			$entry_loop_tmp = str_replace('class="gbcomment"','class="gbcomment" style="display:none !important;"',$entry_loop_tmp);
		}
		// actually replace vars with values
		echo str_replace($vars, $values, $entry_loop_tmp);
 	}
}

// STEP 2.3.5.		Print footer
if($num_entries!=0) {
	$fvars = array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]');
	if($display_previous_next_links == 'none') {
		$fvalues = array('','','','','','', $display_previous_next_links);
	} else {
		$fvalues = array($next_page_link, $next_link, $previous_page_link, $previous_link, $out_of, $of, $display_previous_next_links);
	}
	echo str_replace($fvars, $fvalues, $guestbook_footer);
}

// print no entries
if($num_entries==0) { 
	// "<p style=\"text-align:center;\">{$MOD_GUESTBOOK['NO_MESSAGE']}</p>";
	$vars = array('[NO_ENTRIES]');
	$values = array($MOD_GUESTBOOK['NO_MESSAGE']);
	echo str_replace($vars, $values, $guestbook_no_entries);
}

// close geoip-database
if(defined('GEOIP_DATABASE_LOADED'))
	geoip_close($gi);
