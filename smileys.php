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

// Array of smileys:  'smiley'=>'image'
// One may add, change, or remove smileys.

// For duplicate values (e.g. keys ':)' and ':-)' have both '...smile.gif' as value)
// the first key is used in gbk_page.php, that is by hitting the smile.gif-image
// the user will see :) (the first key) in the message-field.
$smileys = array(
	':)'      => WB_URL.'/modules/guestbook/images/smile/smile.gif',
	':-)'     => WB_URL.'/modules/guestbook/images/smile/smile.gif',
	';)'      => WB_URL.'/modules/guestbook/images/smile/wink.gif',
	';-)'     => WB_URL.'/modules/guestbook/images/smile/wink.gif',
	':P'      => WB_URL.'/modules/guestbook/images/smile/tongue.gif',
	':-P'     => WB_URL.'/modules/guestbook/images/smile/tongue.gif',
	':p'      => WB_URL.'/modules/guestbook/images/smile/tongue.gif',
	'(B)'     => WB_URL.'/modules/guestbook/images/smile/bier.gif',
	'(b)'     => WB_URL.'/modules/guestbook/images/smile/bier.gif',
	':D'      => WB_URL.'/modules/guestbook/images/smile/biggrin.gif',
	':-D'      => WB_URL.'/modules/guestbook/images/smile/biggrin.gif',
	':d'      => WB_URL.'/modules/guestbook/images/smile/biggrin.gif',
	':?'      => WB_URL.'/modules/guestbook/images/smile/confused.gif',
	'=)'      => WB_URL.'/modules/guestbook/images/smile/duivel.gif',
	'(8)'     => WB_URL.'/modules/guestbook/images/smile/gemeen.gif',
	':0'      => WB_URL.'/modules/guestbook/images/smile/hypocrite.gif',
	':joint:' => WB_URL.'/modules/guestbook/images/smile/joint.gif',
	'(J)'     => WB_URL.'/modules/guestbook/images/smile/joint.gif',
	'(j)'     => WB_URL.'/modules/guestbook/images/smile/joint.gif',
	':('      => WB_URL.'/modules/guestbook/images/smile/mad.gif',
	':-('     => WB_URL.'/modules/guestbook/images/smile/mad.gif',
	':@'      => WB_URL.'/modules/guestbook/images/smile/mad.gif',
	':wall:'  => WB_URL.'/modules/guestbook/images/smile/muur.gif',
	':pray:'  => WB_URL.'/modules/guestbook/images/smile/pray.gif',
	':puke:'  => WB_URL.'/modules/guestbook/images/smile/puke.gif',
	':rolleyes:' => WB_URL.'/modules/guestbook/images/smile/rolleyes.gif'
);

/* example - using smileys from fckeditor
$smileys = array(
	':-)' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/regular_smile.gif',
	':)' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/regular_smile.gif',
	':-(' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/sad_smile.gif',
	':(' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/sad_smile.gif',
	':-D' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/teeth_smile.gif',
	';-)' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/wink_smile.gif',
	';)' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/wink_smile.gif',
	':omg:' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/omg_smile.gif',
	':emb:' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/embaressed_smile.gif',
	'O:-)' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/angel_smile.gif',
	':-P' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/tounge_smile.gif',
	':-t' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/angry_smile.gif',
	':->' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/devil_smile.gif',
	'>:->' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/devil_smile.gif',
	'B-)' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/shades_smile.gif',
	":'-(" => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/cry_smile.gif',
	":'(" => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/cry_smile.gif',
	':-o' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/whatchutalkingabout_smile.gif',
	':-|' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/confused_smile.gif',
	':-*' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/kiss.gif',
	':light:' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/lightbulb.gif',
	':heart:' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/heart.gif',
	':cake:' => WB_URL.'/modules/fckeditor/fckeditor/editor/images/smiley/msn/cake.gif',
);
*/

/* One can also load smileys from external server
$smileys = array(
	':-)' => 'http://freesmileys.example.com/images/smiley/regular_smile.gif',
// ...
);
*/
