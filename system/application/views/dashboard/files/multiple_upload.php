<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
*{ margin : 0; padding: 0;}
a img { border : none;}
body {background : #fff;}
div#up_form { padding : 10px;}
h4 { border-bottom : dotted 2px #ccc;padding : 10px;}
p { margin : 5px 0 0 15px;font-size : 0.9em;}

div#up_form a {
	text-decoration : none;
	margin : 3px 0;
	margin-top : 10px;
	outline : none;
}
div#form_div iframe {border:none; height:28px;overflow:hidden;}
input.do_upload { margin-top : 8px;width : auto !important; }
img#loading { vertical-align : middle; visibility : hidden;}
</style>
<script type="text/javascript">
var frameCount = 1;
window.onload =function() {
	var a = document.getElementById('add_file');
	a.onclick = function() {
		if (frameCount >= 5) {
			alert('最大で５ファイルまでです。');
			return;
		}
		var div = document.getElementById('form_div'),
				FL = window.parent.getInstance(),
				ifr = document.createElement('iframe');

		ifr.scrolling = 'no';
		ifr.frameBorder = '0';

		// !!notice
		// IE系はsrc設定後にappendChildするとsrcリクエストがおかしくなる場合がる
		// appendChild後にsrc設定で回避できる
		div.appendChild(ifr);
		ifr.src = '<?php echo page_link();?>dashboard/files/directory_view/multiple_piece';
		frameCount++;
	};

	var sbt = document.getElementById('upload_btn');
	var loading = document.getElementById('loading');
	sbt.onclick = function() {
		this.disabled = true;
		loading.style.visibility = 'visible';
		var ifs = document.getElementById('form_div').getElementsByTagName('iframe'), i = 0, len = ifs.length, timer,
			DOM = window.parent.DOM, currentPath = DOM('ul#sz_dir_tree_path a.current').get(0).parent().readAttr('dir_id'), cw;
		timer = setInterval(function() {
			if (!ifs[i]) {
				loading.style.visibility = 'hidden';
				clearInterval(timer);
			} else {
				cw = ifs[i].contentWindow;
				cw.document.getElementById('upload_path').value = currentPath;
				cw.document.getElementsByTagName('form')[0].submit();
				i++;
			}
		}, 500);

	}

};

// recieve child iframe data
function sendData(data) {
	window.parent.addStack(data);
}
</script>
</head>
<body>
<h4>複数ファイルのアップロード</h4>
<p>最大で一度に5ファイルまでアップロードできます。</p>
<div id="up_form">
	<div id="form_div">
		<iframe src="<?php echo page_link();?>dashboard/files/directory_view/multiple_piece" scrolling="no" frameborder="0"></iframe>
	</div>
	<a href="javascript:void(0)" id="add_file"><?php echo set_image('plus.png', TRUE);?>ファイルを追加</a><br />
	<input type="button" value="アップロード" class="do_upload" id="upload_btn" /><img src="<?php echo file_link();?>images/loading_small.gif" id="loading"/>
</div>
</body>
</html>