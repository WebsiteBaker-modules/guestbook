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

// Include wb class
require_once(WB_PATH.'/framework/class.wb.php');
$wb = new wb('Start', 'start', false, false);
// Include WB functions file
require_once(WB_PATH.'/framework/functions.php');

// check if module language file exists for the language set by the user (e.g. DE, EN)
if(file_exists(WB_PATH .'/modules/guestbook/languages/'.LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/guestbook/languages/'.LANGUAGE .'.php');
} else {
	require_once(WB_PATH .'/modules/guestbook/languages/EN.php');
}

// load geoip-database, if available
if(file_exists(WB_PATH.'/modules/geoip/geoip_functions.php')) {
	require_once(WB_PATH.'/modules/geoip/geoip_functions.php');
	$gi = geoip_open_db(); // defines constant GEOIP_DATABASE_LOADED on success
}

// Validation:		Check if details are correct. If not navigate to main.
if(!(
	isset($_GET['sid']) && is_numeric($_GET['sid'])
	&& isset($_GET['pid']) && is_numeric($_GET['pid'])
	&& isset($_POST['u_name']) && $_POST['u_name']!=''
	&& (
		(defined('ENABLED_ASP') && ENABLED_ASP && isset($_POST['me55age_'.date('W')]) && $_POST['me55age_'.date('W')] != '')
		|| (((defined('ENABLED_ASP') && !ENABLED_ASP) || !defined('ENABLED_ASP')) && isset($_POST['message']) && $_POST['message'] != '')
	))) {
	exit(header('Location: '.WB_URL.PAGES_DIRECTORY));
}

$section_id = (int)$_GET['sid'];
$page_id = (int)$_GET['pid'];

// fetch values from $_POST[], clean values
// name
$name_clean_html = htmlspecialchars(strip_tags($wb->strip_slashes($_POST['u_name'])),ENT_QUOTES);
$name_clean_db   = addslashes(htmlspecialchars(strip_tags($wb->strip_slashes($_POST['u_name'])),ENT_QUOTES));
if($name_clean_html=='') {
	$name_clean_html = $name_clean_db = 'unknown';
}
// homepage
$homepage_clean_html = htmlspecialchars(strip_tags($wb->strip_slashes($_POST['homepage'])),ENT_QUOTES);
$homepage_clean_db   = addslashes(htmlspecialchars(strip_tags($wb->strip_slashes($_POST['homepage'])),ENT_QUOTES));
if($homepage_clean_html=='http://www.') {
	$homepage_clean_html = $homepage_clean_db = '';
}
// email
$email_clean_html = htmlspecialchars(strip_tags($wb->strip_slashes($_POST['email'])),ENT_QUOTES);
$email_clean_db   = addslashes(htmlspecialchars(strip_tags($wb->strip_slashes($_POST['email'])),ENT_QUOTES));
// message
if(defined('ENABLED_ASP') && ENABLED_ASP)
	$tmp = $_POST['me55age_'.date('W')];
else
	$tmp = $_POST['message'];
$message_tainted = $tmp; // ATTN: $message_tainted is NOT cleaned - used in bancheck-module
$message_clean_html = htmlspecialchars(strip_tags($wb->strip_slashes($tmp)),ENT_QUOTES);
$message_clean_db   = addslashes(htmlspecialchars(strip_tags($wb->strip_slashes($tmp)),ENT_QUOTES));
unset($tmp);
if($message_clean_html=='') {
	$message_clean_html = $message_clean_db = $message_tainted = '-empty-';
}

// fetch ip-addr
if(defined('GEOIP_DATABASE_LOADED'))
	$ip_addr = geoip_best_ip();
else $ip_addr = $_SERVER['REMOTE_ADDR'];

if(file_exists(WB_PATH.'/modules/ban/bancheck.php')) {
	$bancheckvar=$message_tainted;
	include WB_PATH.'/modules/ban/bancheck.php';
}

// Advanced Spam Protection
$t = time();
if(defined('ENABLED_ASP') && ENABLED_ASP && (
	($_SESSION['session_started']+ASP_SESSION_MIN_AGE > $t) OR // session too young
	(!isset($_SESSION['comes_from_view_gb'])) OR // user doesn't come from view.php
	(!isset($_SESSION['comes_from_view_gb_time']) OR $_SESSION['comes_from_view_gb_time'] > $t-ASP_VIEW_MIN_AGE) OR // user is too fast
	(!isset($_SESSION['submitted_when']) OR !isset($_POST['submitted_when'])) OR // faked form
	($_SESSION['submitted_when'] != $_POST['submitted_when']) OR // faked form
	($_SESSION['submitted_when'] < $t-43200) OR // form older than 12h
	(!isset($_POST['email-address']) || $_POST['email-address']!='' OR
	 !isset($_POST['url']) || $_POST['url']!='' OR
	 !isset($_POST['name']) || $_POST['name']!='' OR
	 !isset($_POST['comment']) || $_POST['comment']!='') // honeypot-fields
)) {
	exit(header("Location: ".WB_URL.PAGES_DIRECTORY));
}

// Retrieve settings
$query_settings = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_guestbook_settings` WHERE `section_id` = '".$section_id."'");
$settings = $query_settings->fetchRow();
$use_captcha = $settings['use_captcha'];
$auto_approve = $settings['auto_approve'];
$admin_email = $settings['admin_email'];
$store_server_vars = $settings['store_server_vars'];

// Captcha
if( (extension_loaded('gd') && function_exists('imageCreateFromJpeg')) /* Make's sure GD library is installed (for wb <2.7)*/
	|| file_exists(WB_PATH.'/include/captcha/captcha.php') ) { /* wb2.7 */
	if($use_captcha) {
		if(isset($_POST['captcha']) AND $_POST['captcha'] != ''){
			// Check for a mismatch
			if(!isset($_POST['captcha']) OR !isset($_SESSION['captcha']) OR $_POST['captcha'] != $_SESSION['captcha']) {
				$captcha_error = $MESSAGE['MOD_FORM']['INCORRECT_CAPTCHA'];
			}
		} else {
			$captcha_error = $MESSAGE['MOD_FORM']['INCORRECT_CAPTCHA'];
		}
	}
}

if(isset($_SESSION['captcha'])) {
	unset($_SESSION['captcha']);
}


if(isset($captcha_error)) {
	$_SESSION['gb']['message'] = $message_clean_html;
	$_SESSION['gb']['email'] = $email_clean_html;
	$_SESSION['gb']['homepage'] = $homepage_clean_html;
	$_SESSION['gb']['name'] = $name_clean_html;
	exit(header('Location: '.WB_URL."/modules/guestbook/gstbk_add.php?sid=$section_id&pid=$page_id&add=".(int)$_SESSION['comes_from_view_gb']."&captcha=true"));

} else {

	if(isset($_SESSION['gb'])) {
		unset($_SESSION['gb']);
	}
	if(defined('ENABLED_ASP') && ENABLED_ASP) {
		unset($_SESSION['comes_from_view_gb']);
		unset($_SESSION['comes_from_view_gb_time']);
		unset($_SESSION['submitted_when']);
	}

	
	// get position
	// Include the ordering class
	require(WB_PATH.'/framework/class.order.php');
	$order = new order(TABLE_PREFIX."mod_guestbook", 'position', 'id', 'section_id');
	$position = $order->get_new($section_id);
	$entry_when = time();

	// store server-vars?
	$server_vars = '';
	if($store_server_vars) {
		foreach($_SERVER as $k=>$v) {
			if(is_array($v)) continue;
			$var = htmlspecialchars($k, ENT_QUOTES);
			$val = htmlspecialchars($v, ENT_QUOTES);
			$server_vars .= "<strong>$var</strong> = $val<br />";
			$server_vars = addslashes($server_vars);
		}
	}

	//Insert into DB
	$query = $database->query("
		INSERT INTO `".TABLE_PREFIX."mod_guestbook`
		(`section_id`, `page_id`, `name`, `email`, `homepage`, `message`, `posted_when`, `position`, `approved`, `ip_addr`, `server_vars`)
		VALUES
		('$section_id','$page_id', '$name_clean_db', '$email_clean_db', '$homepage_clean_db', '$message_clean_db', '$entry_when', '$position', '$auto_approve', INET_ATON('$ip_addr'), '$server_vars')
	");
	if($database->is_error()) echo 'Error: '.$database->get_error();

	// fetch guestbook-URL
	$link = WB_URL;
	if($query_link = $database->query("SELECT link FROM `".TABLE_PREFIX."pages` WHERE `page_id` = '$page_id'"))
		if($row = $query_link->fetchRow())
			$link = WB_URL.PAGES_DIRECTORY.$row['link'].PAGE_EXTENSION;

	//send mail
	if($admin_email!=''){
		$mail_subject = $MOD_GUESTBOOK['MAILSUBJECT'];
		$mail_message = $MOD_GUESTBOOK['MAILMESSAGE'];
		// append link to message
		$mail_message .= "<a href=\"$link\" target=\"_blank\">$link</a>";
		// add guestbook-text to message
		$mail_message .= "\r\n\r\n$name_clean_html [$ip_addr] ($email_clean_html - $homepage_clean_html)\r\n$message_clean_html";
		$wb->mail(SERVER_EMAIL,$admin_email,$mail_subject,$mail_message);
	}
	
	// close geoip-database
	if(defined('GEOIP_DATABASE_LOADED'))
		geoip_close($gi);

		// jump back to guestbook
		exit(header("Location: $link"));
	
}

