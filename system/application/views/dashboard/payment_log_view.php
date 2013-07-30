<?php echo $this->load->view('dashboard/dashboard_header');?>
<!--  dashboard contents -->
<!-- h2 stays for breadcrumbs -->
<h2>課金ログ</h2>
<div id="main">
<?php if (!empty($msg)):?>
  <div class="message"><?php echo $msg;?></div>
<?php endif;?>
<?php echo form_open('dashboard/payment_log')?>
<table border="0">
<tr>
	<td>DoCoMo:<?php echo number_format($docomo_cnt)?>件</td>
	<td>AU:<?php echo number_format($au_cnt)?>件</td>
	<td>softbank:<?php echo number_format($softbank_cnt)?>件</td>
	<td align="right">絞り込み条件：<?php echo form_dropdown('carrier_kn', $options,$carrier_kn,'name="payment_form" onchange="this.form.submit();"')?></td>
</tr>
</table>
<?php echo form_close()?>
  <table cellpadding="0" cellspacing="0">
    <tr>
      <td>識別UID</td>
      <td>キャリア</td>
      <td>ユーザーAgent</td>
      <td>登録日</td>
    </tr>

    <?php if (count($entry) > 0):?>
    <?php foreach ($entry as $key => $value):?>
    <tr<?php if($key %2 === 0){ echo ' class="odd"';}?>>
      <td class="tooltip">
          <?php echo $value->uid;?>
      </td>
      <td><?php echo $value->carrier?></td>
	  <td><?php echo $value->user_agent?></td>
	  <td><?php echo $value->create_date?></td>
    </tr>
    <?php endforeach;?>
    <?php else:?>
    <tr>
      <td colspan="4" >登録データがありません。</td>
    </tr>
    <?php endif;?>
  </table>
  <p class="pagination"><?php echo $pagination;?></p>
  <br />
  <br />
  <?php echo form_open('dashboard/payment_log/download')?>
  <?php echo form_submit('csv_down','CSVダウンロード')?>
  <?php echo form_close()?>
</div>
<!-- // #main -->

<div class="clear"></div>
</div>
<!-- // #container -->
</div>
<!-- // #containerHolder -->

<p id="footer"></p>
</div>
<!-- // #wrapper -->
</body>
</html>
