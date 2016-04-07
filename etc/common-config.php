<?php
/* These configuration settings are common to all parts of the
 * DOMjudge system and included by the specific configuration files.
 */

function conf($key,$default) {
	$value = $default;
	$e=getenv(mb_strtoupper($key));
	if($e!=null)
		$value=$e;
	define($key,$value);
}
 
conf('DEBUG', 0);
conf('SUBMITCLIENT_ENABLED', 'yes');

conf('productName', 'Daavar');
conf('version', '4.0');

conf('SHOW_ERRORS', true);

conf('AllowSignup', true);
conf('AllowForget', true);

conf('SignupAutoEnable', false);

conf('SignupDefaultCategory', 2);
conf('SignupDefaultUserRole', 3);

conf('AllowEditUserProfile', true);

conf('ShowOtherTeamSubmissions', true);

conf('MaxSubmissions', 20);

conf('site_url', 'http://daavar.icpc.aut.ac.ir/test'.'/');

//can be false
conf('homepage_url', 'http://icpc.aut.ac.ir');
conf('sender_email', 'AUT ICPC Daavar Judge <icpc@aut.ac.ir>');
