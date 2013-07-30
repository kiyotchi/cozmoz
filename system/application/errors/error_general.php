<?php
	require(APPPATH . 'libraries/seezoo/seezoo_translation.php');
	$conf =& get_config();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $heading;?></title>
<style type="text/css">
div#content {
	width : 500px;
	height : 400px;
	margin : 50px auto 30px auto;
	background : url(<?php echo $conf['base_url']?>images/status/500_error.gif) top left no-repeat;
}
div.content_inner {
	padding-top : 180px;
	text-align : center;
	line-height : 1.8;
}
div#error_footer {
	margin-top : 10px;
	border-top : dotted 3px #1E90FF;
	padding-top : 30px;
	text-align : center;
}
div#error_footer a {
	margin : 30px;
	text-decoration : none;
	color : #009;
	font-family : Arial;
}
div#error_footer a:hover {
	text-decoration : underline;
}
</style>
</head>
<body>
	<div id="content">
		<div class="content_inner">
		<p>
			<?php echo sz_translate($message);?>
		</p>
		</div>
		<div id="error_footer">
			<a href="#" id="history_back">&lt;&nbsp;戻る</a>
			<a href="./" id="back_to_top">&laquo;&nbsp;トップに戻る</a>
		</div>
	</div>
</body>
</html>