<?php
if ( !defined('LIBDIR') ) die ("LIBDIR not defined.");

if( DEBUG & DEBUG_TIMINGS ) {
	require_once(LIBDIR . '/lib.timer.php');
}

require_once(LIBDIR . '/lib.error.php');
require_once(LIBDIR . '/lib.misc.php');
require_once(LIBDIR . '/lib.dbconfig.php');
require_once(LIBDIR . '/use_db.php');

// Initialize default timezone to system default. PHP >= 5.3 generates
// E_NOTICE warning messages otherwise.
@date_default_timezone_set(@date_default_timezone_get());

// Set for using mb_* functions:
mb_internal_encoding(DJ_CHARACTER_SET);

//Get current page
//global $currPage;
//if(preg_match('/([^\/]*)?\.php/',$_SERVER["SCRIPT_FILENAME"],$matches))
//    $currPage=array_values($matches)[1];
//else
//    $currPage='';

//Minify content !
//TODO : fix <pre> Tags !
//function minify()
//{
//    ob_start(function ($html) {
//        $html = preg_replace(array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'), array('>', '<', '\\1'), $html);
//        $html = preg_replace('/<!--[^\[](.*)-->/Uis', '', $html);
//        return $html;
//    });
//}
//minify();
