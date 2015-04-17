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

// Make sure page cannot be accessed directly
if(!defined('WB_URL'))	{
	exit(header('Location: ../index.php'));
}
	
// check if module language file exists for the language set by the user (e.g. DE, EN)
if(file_exists(WB_PATH .'/modules/guestbook/languages/'.LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/guestbook/languages/'.LANGUAGE .'.php');
} else {
	require_once(WB_PATH .'/modules/guestbook/languages/EN.php');
}

// check if frontend.css file needs to be included into the <body></body> of view.php
if((!function_exists('register_frontend_modfiles') || !defined('MOD_FRONTEND_CSS_REGISTERED')) &&  file_exists(WB_PATH .'/modules/guestbook/frontend.css')) {
   echo '<style type="text/css">';
   include(WB_PATH .'/modules/guestbook/frontend.css');
   echo "\n</style>\n";
}

//create variables if they are not defined
if(!isset($_SESSION['gb']))             $_SESSION['gb']=array();
if(!isset($_SESSION['gb']['name']))     $_SESSION['gb']['name'] = '';
if(!isset($_SESSION['gb']['email']))    $_SESSION['gb']['email'] = '';
if(!isset($_SESSION['gb']['homepage'])) $_SESSION['gb']['homepage'] = '';
if(!isset($_SESSION['gb']['message']))  $_SESSION['gb']['message'] = '';

// STEP 1:	get the Settings for this Section
$query_settings = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_guestbook_settings` WHERE `section_id` = '".SECTION_ID."'");
$settings = $query_settings->fetchRow();
$email_required = $settings['email_required'];
$entries_per_page = $settings['entries_per_page'];
$show_unused_fields = $settings['show_unused_fields'];
$use_captcha = $settings['use_captcha'];
$show_smiley = $settings['show_smiley'];
$template = $settings['add_entry'];

if ($settings['ordering'] == '1') {
    $ordering = 'DESC';
} else {
    $ordering = 'ASC';
}
if ($email_required == '1') {
    $star_email = '<span class="required">*</span>';
} else {
    $star_email = '';
}

// JavaScripts to check form and addsmileys
if (isset($_GET['captcha']) && $_GET['captcha'] =="true"){ ?>
	<script language="JavaScript"  type="text/javascript">
		alert( "<?php echo $MOD_GUESTBOOK['INCORRECT_VERIFICATION'] ?>" );
	</script>
<?php }
?>
<script language="JavaScript" type="text/javascript">
// Philipp Dolder
function checkForm(form) {
    // Check if the Name is entered
    if (form.u_name.value == "") {
        alert( "<?php echo $MOD_GUESTBOOK['PLEASE_ENTER_NAME']; ?>" );
        form.u_name.focus();
        return false;
    }
    <?php
    // Check if the Email is entered if required
    if ($email_required == '1') {
        ?>
        if (form.email.value == "") {
            alert( "<?php echo $MOD_GUESTBOOK['PLEASE_ENTER_EMAIL']; ?>" );
            form.email.focus();
            return false;
        }
    <?php
    } ?>
    // Check if the Email is valid
  	regex = /^[a-zA-Z][\w\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/;
  	if(!regex.test(form.email.value) && !(form.email.value == "")) {
  		alert( "<?php echo $MOD_GUESTBOOK['EMAIL_INVALID']; ?>");
		form.email.focus();
		return false;
  	}
    
    // Check if message is entered
    if (form.message.value == "") {
        alert( "<?php echo $MOD_GUESTBOOK['PLEASE_ENTER_MESSAGE']; ?>" );
        form.message.focus();
        return false;
    }
    return true;
}

function addsmiley(code)  {
    var pretext = document.forms['guestbook_form'].message.value;
      this.code = code;
      document.forms['guestbook_form'].message.value = pretext + code;
}
</script>
<?php

// get all Values for [FIELDS]
$name_str = $MOD_GUESTBOOK['NAME'].'<span class="required">*</span>';
$name_input = 'name="u_name" value="'.($_SESSION['gb']['name']?$_SESSION['gb']['name']:'').'"';
$email_str = $MOD_GUESTBOOK['EMAIL'].$star_email;
$email_input = 'name="email" value="'.($_SESSION['gb']['email']?$_SESSION['gb']['email']:'').'"';
$website_str = $MOD_GUESTBOOK['WEBSITE'];
$website_input = 'name="homepage" value="'.($_SESSION['gb']['homepage']?$_SESSION['gb']['homepage']:'http://www.').'"';
$message_str = $MOD_GUESTBOOK['MESSAGE'].'<span class="required">*</span>';
$message = ($_SESSION['gb']['message']?$_SESSION['gb']['message']:'');
$captcha_str = $MOD_GUESTBOOK['VERIFICATION'].'<span class="required">*</span>'.':';
$submit_str = $MOD_GUESTBOOK['SUBMIT'];
$reset_str = $MOD_GUESTBOOK['RESET'];
if(defined('ENABLED_ASP') && ENABLED_ASP) {
	$reset_onclick = 'javascript: window.location = \''.WB_URL.'/modules/guestbook/gstbk_add.php?sid='.SECTION_ID.'&amp;pid='.PAGE_ID.'&amp;add='.(int)$_SESSION['comes_from_view_gb'].'\';';
	$gb_form = 'id="guestbook_form" onsubmit="return checkForm(this);" action="'.WB_URL.'/modules/guestbook/gstbk_save.php?sid='.SECTION_ID.'&amp;pid='.PAGE_ID.'&amp;add='.(int)$_SESSION['comes_from_view_gb'].'" method="post"';
	$message_input = 'id="message" name="me55age_'.date('W').'"';
} else {
	$reset_onclick = 'javascript: window.location = \''.WB_URL.'/modules/guestbook/gstbk_add.php?sid='.SECTION_ID.'&amp;pid='.PAGE_ID.'\';';
	$gb_form = 'id="guestbook_form" onsubmit="return checkForm(this);" action="'.WB_URL.'/modules/guestbook/gstbk_save.php?sid='.SECTION_ID.'&amp;pid='.PAGE_ID.'" method="post"';
	$message_input = 'id="message" name="message"';
}
$smileys_imgs = '';
if($show_smiley=='1') {
	require_once(WB_PATH.'/modules/guestbook/smileys.php');
	foreach(array_unique($smileys) as $sm_code=>$sm_url) {
		$smileys_imgs .= '<img class="smileys" src="'.$sm_url.'" alt="'.$sm_code.'" onclick="addsmiley(\''.$sm_code.'\')"/> ';
	}
}
$asp_fields = '';
if(defined('ENABLED_ASP') && ENABLED_ASP) {
	$t=time();
	$_SESSION['submitted_when']=$t;
	$asp_fields = '
	<input type="hidden" name="submitted_when" value="'.$t.'" />
	<p class="nixhier">
	name:
	<label for="name">Leave this field name blank:</label>
	<input id="name" name="name" size="60" value="" /><br />
	email address:
	<label for="email">Leave this field email-address blank:</label>
	<input id="email-address" name="email-address" size="60" value="" /><br />
	URL:
	<label for="url">Leave this field url blank:</label>
	<input id="url" name="url" size="60" value="" /><br />
	Comment:
	<label for="comment">Leave this field comment blank:</label>
	<textarea id="comment" name="comment"></textarea><br />
	</p>';
}
$captcha = '';
if($use_captcha) {
	//check if the captcha from WB 2.7 is available
	if(file_exists(WB_PATH.'/include/captcha/captcha.php')) {
		require_once(WB_PATH.'/include/captcha/captcha.php');
		ob_start();
		call_captcha();
		$captcha = ob_get_contents();
		ob_end_clean();
	} else {
		// use this captcha instead 
		$_SESSION['captcha'] = '';
		mt_srand((double)microtime()*100000);
		$n = mt_rand(1,3);
		switch ($n) {
		case 1:
			mt_srand((double)microtime()*1000000);
			$x = mt_rand(1,9);
			$y = mt_rand(1,9);
			$_SESSION['captcha'] = $x + $y;
			$cap = $x.' '.$MOD_GUESTBOOK['ADDITION'].' '.$y; 
			break; 
		case 2:
			mt_srand((double)microtime()*1000000);
			$x = mt_rand(10,20);
			$y = mt_rand(1,9);
			$_SESSION['captcha'] = $x - $y; 
			$cap = $x.' '.$MOD_GUESTBOOK['SUBTRAKTION'].' '.$y; 
			break;
		case 3:
			mt_srand((double)microtime()*1000000);
			$x = mt_rand(2,10);
			$y = mt_rand(2,5);
			$_SESSION['captcha'] = $x * $y; 
			$cap = $x.' '.$MOD_GUESTBOOK['MULTIPLIKATION'].' '.$y; 
			break;
		}
		$captcha = $cap.' = <input type="text" name="captcha" maxlength="2" style="width:20px" />&nbsp;&nbsp;'.$MOD_GUESTBOOK['VERIFICATION_INFO'];
	}
} else {
	$captcha_str = '';
}

// display form
$vars = array('[GB_FORM]','[ASP_FIELDS]','[NAME_STR]','[NAME_INPUT]','[EMAIL_STR]','[EMAIL_INPUT]','[WEBSITE_STR]','[WEBSITE_INPUT]','[SMILEYS]','[MESSAGE_STR]','[MESSAGE_INPUT]','[MESSAGE]','[CAPTCHA_STR]','[CAPTCHA]','[SUBMIT_STR]','[RESET_STR]','[RESET_ONCLICK]');
$vals = array($gb_form,$asp_fields,$name_str,$name_input,$email_str,$email_input,$website_str,$website_input,$smileys_imgs,$message_str,$message_input,$message,$captcha_str,$captcha,$submit_str,$reset_str,$reset_onclick);
echo str_replace($vars, $vals, $template);

