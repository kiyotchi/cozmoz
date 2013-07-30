<?php echo xml_define();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<?php echo $this->load->view('header_required');?>
<link href="<?php echo $template_path; ?>css/common.css" rel="stylesheet" type="text/css" />
</head>

<body>
<!--↓wrap↓-->
<div id="wrap">


<!--↓header↓-->
<div id="header">
	<h1><a href="#"><img src="<?php echo $template_path;?>images/logo.png" alt="音生総合病院" width="211" height="36" /></a></h1>
	<div class="tel">
		<img src="<?php echo $template_path;?>images/telimg.png" alt="000-0000　受付時間:午前9:00〜12:00 午後13:00〜17:00" width="253" height="38" />
	</div>
</div> 
<!--↑header↑-->

<!--↓navi↓-->
<div id="navi">
	<?php echo $this->load->area('navi');?> 
</div>
<!--↑navi↑-->


<!--↓ic_wrap↓-->
<div id="ic_wrap">


<div class="il_contents">
<?php echo set_content();?>
</div>
<!--↑leftcontents↑-->


<!--↓rightcontents↓-->
<div class="ir_contents">
<?php echo set_menu();?>
</div>
<!--↑rightcontents↑-->

</div>
<!--↑ic_wrap↑-->

<p class="pagetop">
<a href="#">ページの先頭へ</a>
</p>


<!--↓footer↓-->
<div id="footer">
	<?php echo $this->load->area('footer_navi');?> 
</div>
<!--↑footer↑-->

<address>
<img src="<?php echo $template_path;?>images/footerlogo.png" alt="音生総合病院" width="149" height="25" />&nbsp;&nbsp;〒000-0000&nbsp;&nbsp; 愛知県名古屋市中区金山32-1&nbsp;&nbsp;tel:000-0000-0000&nbsp;&nbsp;fax:000-0000-0000
</address>

<p id="copyright">Copyright &copy; 2011  All Rights Reserved.</p>

</div>
<!--↑wrap↑-->
<?php echo $this->load->view('footer_required');?>
</body>
</html>
