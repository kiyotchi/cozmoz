<?php
/*
 * CodeIgniter default Error message has not translated.
 * So that, we translate there messages manually.
 * #Note: filename and line number is CodeIgniter1.7.2 currenty.
 * There is a few difference on after versions or your own customized files..
 */

// original messages
$original_messages = array(
	'The URI you submitted has disallowed characters.',  // system/libraries/URI.php line 193
	'Unable to load your default controller.  Please make sure the controller specified in your Routes.php file is valid.' // system/codeigniter/CodeIgniter.php line 155
);

// manual translations
$translates = array(
		'指定されたURLへのアクセスは許可されていません。',
		'必要なコントローラクラスがロード出来ませんでした。',
);