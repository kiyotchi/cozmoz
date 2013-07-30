<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3c.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" >
<html lang="ja" style="overflow-x:hidden;">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="1" />
<meta name="keyword" content="ﾊﾞｲｸ,ﾊﾞｲｸﾊﾟｰﾂ,中古ﾊﾞｲｸ,ﾊﾞｲｸ用品,ﾍﾙﾒｯﾄ,ﾊﾞｲｸｼｮｯﾌﾟ,ﾊﾞｲｸﾌﾞﾛｽ"/>
<meta name="description" content="ﾊﾞｲｸﾌﾞﾛｽﾌﾟﾚﾐｱﾑならﾊﾞｲｸｶｽﾀﾑ･ﾒﾝﾃﾅﾝｽ･ﾂｰﾘﾝｸﾞなどのﾊﾞｲｸﾗｲﾌ情報満載"/>
<meta name="google-site-verification" content="LXyKVj1hV29oOeNo0mvRFKhzaL-UR2sWNoKMtgJKCBo" />
<meta name="robots" content="index,follow" />
<title>ﾊﾞｲｸｶｽﾀﾑ･ﾒﾝﾃﾅﾝｽ･ﾂｰﾘﾝｸﾞ情報はﾊﾞｲｸﾌﾞﾛｽﾌﾟﾚﾐｱﾑ</title>
<link rel="canonical" href="http://mobile.bikebros.co.jp"/>
</head>

<body>
<a name="pagetop" id="pagetop"></a>
<div style="font-size:xx-small;">

	<div style="text-align:center;" align="center">
    <img src="<?php echo base_url()?>templates/img/prelogo.jpg" width="240" height="60" alt="ﾊﾞｲｸﾌﾞﾛｽﾌﾟﾚﾐｱﾑ" /></div>

<?php if(validation_errors() != ''): ?>
<?php echo validation_errors(); ?>
<?php else:?>
<!-- #menu トップメニュー -->
<div id="detail">
<hr size="1" color="#333333" style="border-colo:#333333;" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">

  <tr>

    <td bgcolor="#000000"><img src="<?php echo base_url()?>templates/img/icon-midashi.jpg" width="16" height="16" /><font color="#FFFFFF" size="-1"><?php echo isset($entry->sz_blog_category_id) ? $category_data[$entry->sz_blog_category_id] : ''?></font></td>

  </tr>

  <tr>

    <td bgcolor="#E6E6E6"><font size="-6" color="#db0000"><?php echo isset($entry->title) ? '┗'.emoji_convert($entry->title) : ''?></font></td>

  </tr>

</table>
<div><?php echo mb_convert_kana(emoji_convert($entry->body), "k", "utf-8")?></div>
</div>
<?php if(isset($entry->cus_title1) && $entry->cus_title1 != ''):?>
<a name="gallery"></a>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td bgcolor="#000000"><img src="<?php echo base_url()?>templates/img/icon-midashi.jpg" width="16" height="16" /><font color="#FFFFFF" size="-1">ｶｽﾀﾑｷﾞｬﾗﾘｰ</font></td>
  </tr>
  <tr>
    <td bgcolor="#E6E6E6"><font size="-6" color="#333333">┗ｶｽﾀﾑの詳細をﾁｪｯｸ!!</font></td>
  </tr>
</table>
<?php
		for($i = 1; $i <= 15; $i++):
        	$cus_title = 'cus_title'.$i;
        	$cus_hp_thumbnail = 'cus_hp_thumbnail'.$i;
        	$cus_img_extension = 'cus_img_extension'.$i;
        	$cus_comment = 'cus_comment'.$i;

        	if($entry->$cus_title != ''):
?>
<table border="0" cellspacing="0" cellpadding="2px" width="100%">
	<tr>
		<td>
        <a href="#"><img src="<?php echo page_link('d_files/tmp_custom/'.$entry->sz_blog_id.'/'.$i.'/cus_hp_thumbnail')?>" alt="<?php echo $entry->$cus_title?>" /></a>
		</td>
		<td>
<font size="-6"><?php echo emoji_convert($entry->$cus_title)?></font>
		</td>
	</tr>
</table>
		     <hr size="1" color="#E6E6E6" style="border-colo:#E6E6E6;margin:3px 0;" />

<?php
			endif;
		endfor;
endif;
?>

<div><?php echo isset($entry->etc_html) ? emoji_convert($entry->etc_html) : ''?></div>
<?php endif;?>
<div style="text-align:right; margin-bottom:3px;" align="right"><font size="-6"><?php echo emoji_convert('[m:145]')?><a href="#pagetop">ﾍﾟｰｼﾞTOPへ</a></font></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">

  <tr>

    <td><img src="<?php echo file_link()?>/templates/img/midashi05.jpg" width="240" height="27" alt="ﾌﾟﾚﾐｱﾑｺﾝﾃﾝﾂ" /></td>

  </tr>

  <tr>

    <td bgcolor="#e6e6e6"><font size="-6">└ﾊﾞｲｸﾌﾞﾛｽﾌﾟﾚﾐｱﾑの有料会員限定ｺﾝﾃﾝﾂ<?php echo emoji_convert('[m:150]')?></font></td>

  </tr>
</table>
  <font color="#999999">┣</font> <a href="#"><font color="#db0000">ﾌﾟﾚﾐｱﾑﾂｰﾘﾝｸﾞ</font></a><br />
  <font color="#999999">┣</font> <a href="#"><font color="#db0000">ﾌﾟﾚﾐｱﾑﾒﾝﾃﾅﾝｽ</font></a><br />
  <font color="#999999">┣</font> <a href="#"><font color="#db0000">ﾌﾟﾚﾐｱﾑｶｽﾀﾑ</font></a><br />
  <font color="#999999">┗</font> <a href="#"><font color="#db0000">ﾌﾟﾚﾐｱﾑ待受け</font></a><br />
<br />
<!-- #footer　フッタースペース -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td bgcolor="#000000" style="font-size:xx-small;"><?php echo emoji_convert('[m:191]')?> <font color="#ffffff">ﾊﾞｲｸﾌﾞﾛｽﾌﾟﾚﾐｱﾑﾒﾆｭｰ</font></td>
  </tr>
  <tr>

    <td bgcolor="#333333" style="font-size:xx-small;">

           <?php echo emoji_convert('[m:80]')?> <a href="#"><font color="#ffffff" size="-6">ﾏｲﾒﾆｭｰ登録</font></a><font color="#ffffff" size="-6"> / </font><a href="#"><font color="#ffffff" size="-6">解約</font></a><br />

           <?php echo emoji_convert('[m:75]')?> <a href="#"><font color="#ffffff" size="-6">対応端末</font></a><br />

           <?php echo emoji_convert('[m:190]')?> <a href="#"><font color="#ffffff" size="-6">特定商取引法に基づく表示</font></a><br />

           <?php echo emoji_convert('[m:63]')?> <a href="#"><font color="#ffffff" size="-6">利用規約</font></a><br /><br />

           <?php echo emoji_convert('[m:38]')?> <a href="#"><font color="#ffffff" size="-6">ﾊﾞｲｸﾌﾞﾛｽﾌﾟﾚﾐｱﾑTOP</font></a><br />

           <?php echo emoji_convert('[m:134]')?> <a href="#" accesskey="0"><font color="#ffffff" size="-6">ﾊﾞｲｸﾌﾞﾛｽTOP</font></a>

    </td>

  </tr>

  <tr>
    <td bgcolor="#000000" align="center" style="text-align:center;"><font color="#999999" size="-6">(C)BikeBros. Inc.<br />All Right Reserved.</font></td>
  </tr>
</table>
</div>
</body>
</html>

