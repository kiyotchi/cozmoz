<?php  $conf =& get_config();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>503 Service Unavailable</title>
<style type="text/css">
div#content {
	width : 500px;
	height : 400px;
	margin : 50px auto 30px auto;
	background : url(<?php echo $conf['base_url'];?>images/status/503.gif) top left no-repeat;
}
div.content_inner {
	padding-top : 160px;
	text-align : center;
	line-height : 1.8;
}
div#error_footer {
	margin-top : 10px;
	border-top : dotted 3px #1E90FF;
	padding-top : 30px;
	text-align : center;x
}
div#error_footer p {
	line-height : 1.4;
	opacity : 0;
	filter : alpha(opacity=0);
	margin : 0;
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
<script type="text/javascript" src="<?php echo $conf['base_url'];?>js/config/base.config.js"></script>
<script type="text/javascript" src="<?php echo $conf['base_url'];?>js/flint.dev.js"></script>
<script type="text/javascript">
	FL.event.set(document, 'DOMReady', function() {
		function createTopLink() {
			FL.ajax.head('<?php echo $conf['base_url'];?>', {
				success: function(o) {
					if (o.status == 200) {
						var html = ['メンテナンスが終了しました。お待たせしました。<br /><a href="<?php echo $conf['base_url'];?>">&laquo;&nbsp;トップへ戻る</a>'];
						DOM.create('p').appendTo(DOM.id('error_footer'))
										.html(html.join(''))
										.animate('appear');
					} else {
						setTimeout(createTopLink, 1000 * 10);
					}
				}
			});
		}
		setTimeout(createTopLink, 1000 * 10);
	});
</script>
</head>
<body>
	<div id="content">
		<div class="content_inner">
		<p>
			現在、メンテナンス中です。<br />
			しばらく時間を置いてからアクセスしてください。
		</p>
		</div>
		<div id="error_footer">
		&nbsp;
		</div>
	</div>
</body>
</html>