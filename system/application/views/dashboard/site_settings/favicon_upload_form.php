<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
*{ margin : 0; padding: 0;}
body { background : #fbfbfb;}
#up_form { float : left;}
#up_form form { display : inline;}
form input { text-align : left;}
label { font-size : 12px;}
br.clear { clear : both;}
</style>
<script type="text/javascript">
	window.onload = function() {
		var sub = document.getElementById('add');
		sub.disabled = true;
		var file = document.getElementsByName('upload_data')[0];
		file.onchange = function() {
			if (!/.+\.png$|.+\.gif$|.+\.ico$/.test(this.value)) {
				alert('選択されたファイルはicoファイルに変換できません。');
				sub.disabled = true;
			} else {
				sub.disabled = (this.value == '') ? true : false;
			}
		};
		sub.onclick = function() {
			var spn = window.parent.document.getElementById('favicon_area'),
				fav = spn.getElementsByTagName('img');

			if (fav.length > 0) {
				if (!confirm('現在登録されているアイコンを上書きします。よろしいですか？')) {
					return false;
				}
			}
			this.disabled = true;
			this.parentNode.submit();
		};
		window.onunload = function() {
				window.load = null;
				window.onunload = null;
				file.onchange = null;
				sub.onclick = null;
		};
	};
	<?php if (isset($error)):?>
	alert('<?php echo $error;?>');
	<?php elseif(isset($success)):?>
	window.parent.sendData();
	<?php endif;?>
</script>
</head>
<body>
<div id="up_form">
	<?php echo form_open_multipart('dashboard/site_settings/base/do_favicon_upload');?>
	<label>faviconファイルの選択：<input type="file" name="upload_data" value="" /></label>
	<input type="submit" value="追加" id="add" />
	<?php echo form_close();?>
</div>
<br class="clear" />
</body>
</html>