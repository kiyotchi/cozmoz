<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
*{ margin : 0; padding: 0;}
form input { text-align : right;}
</style>
<?php if (isset($data)):?>
<script type="text/javascript">
window.parent.sendData(<?php echo $data;?>);
</script>
<?php endif;?>
</head>
<body>
<div id="up_form">
	<?php if (isset($complete)):?>
	<?php if ($complete === 1):?>
	<span class="complete"><?php echo set_image('check.gif', TRUE);?>成功</span>
	<?php elseif ($complete === 0):?>
	<span class="complete"><?php echo set_image('delete.png', TRUE);?>失敗</span>
	<?php else:?>
	<span class="complete"><?php echo set_image('back.png', TRUE);?>スキップしました。</span>
	<?php endif;?>
	<?php else:?>
	<?php echo form_open_multipart('dashboard/files/directory_view/multiple_piece');?>
	<input type="file" name="upload_data" value="" />
	<input type="hidden" name="upload_path" value="" id="upload_path" />
	<input type="hidden" name="upload_handle" value="1" />
	<?php echo form_close();?>
	<?php endif;?>
</div>
</body>
</html>