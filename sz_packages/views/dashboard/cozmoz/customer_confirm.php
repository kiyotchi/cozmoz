<?php echo $this->load->view('dashboard/dashboard_header');?>


<?php echo StaticV::jQuery(); ?>
<script type="text/javascript">
	$(document).ready(function(){
		
		INTERMediator.construct(true);
		
	});
</script>



<!-- h2 stays for breadcrumbs -->
<h2>顧客管理</h2>

<div id="main">
  <h3>顧客追加</h3>
  
  <form action="<?php echo site_url('dashboard/cozmoz/customer/repeat');?>" method="post">
  <table>
	<tbody class="_im_post">
		<tr>
			<th>会社名/屋号</th>
			<td>
				<?php echo set_value( 'company' ); ?>
				<?php echo set_value( 'honorific' ); ?>
				<input type="hidden" name="company" value="<?php echo set_value( 'company' ); ?>" class="IM[<?php echo $cutomer_table; ?>@company]" />
				<input type="hidden" name="honorific" value="<?php echo set_value( 'honorific' ); ?>" class="IM[<?php echo $cutomer_table; ?>@honorific]" />
			</td>
		</tr>
		<tr>
			<th>お名前</th>
			<td>
				<?php echo set_value( 'name1' ); ?>
				<?php echo set_value( 'name2' ); ?>
				<input type="hidden" name="name1" value="<?php echo set_value( 'name1' ); ?>" class="IM[<?php echo $cutomer_table; ?>@name1]" />
				<input type="hidden" name="name2" value="<?php echo set_value( 'name2' ); ?>" class="IM[<?php echo $cutomer_table; ?>@name2]" />
			</td>
		</tr>
		<tr>
			<th>ふりがな</th>
			<td>
				<?php echo set_value( 'kana1' ); ?>
				<?php echo set_value( 'kana2' ); ?>
				<input type="hidden" name="kana1" value="<?php echo set_value( 'kana1' ); ?>" class="IM[<?php echo $cutomer_table; ?>@kana1]" />
				<input type="hidden" name="kana2" value="<?php echo set_value( 'kana2' ); ?>" class="IM[<?php echo $cutomer_table; ?>@kana2]" />
			</td>
		</tr>
		<tr>
			<th>TEL</th>
			<td>
				<?php echo set_value( 'tel' ); ?>
				<input type="hidden" name="tel" value="<?php echo set_value( 'tel' ); ?>" class="IM[<?php echo $cutomer_table; ?>@tel]" />
			</td>
		</tr>
		<tr>
			<th>FAX</th>
			<td>
				<?php echo set_value( 'fax' ); ?>
				<input type="hidden" name="fax" value="<?php echo set_value( 'fax' ); ?>" class="IM[<?php echo $cutomer_table; ?>@fax]" />
			</td>
		</tr>
		<tr>
			<th>メールアドレス</th>
			<td>
				<?php echo set_value( 'mailaddress' ); ?>
				<input type="hidden" name="mailaddress" value="<?php echo set_value( 'mailaddress' ); ?>" class="IM[<?php echo $cutomer_table; ?>@mailaddress]" />
			</td>
		</tr>
		<tr>
			<th>住所</th>
			<td>
				<?php echo set_value( 'zip' ); ?><br />
				<?php echo set_value( 'pref' ); ?><br />
				<?php echo set_value( 'address1' ); ?><br />
				<?php echo set_value( 'address2' ); ?><br />
				<?php echo set_value( 'address3' ); ?>
				<input type="hidden" name="zip" value="<?php echo set_value( 'zip' ); ?>" class="IM[<?php echo $cutomer_table; ?>@zip]" />
				<input type="hidden" name="pref" value="<?php echo set_value( 'pref' ); ?>" class="IM[<?php echo $cutomer_table; ?>@pref]" />
				<input type="hidden" name="address1" value="<?php echo set_value( 'address1' ); ?>" class="IM[<?php echo $cutomer_table; ?>@address1]" />
				<input type="hidden" name="address2" value="<?php echo set_value( 'address2' ); ?>" class="IM[<?php echo $cutomer_table; ?>@address2]" />
				<input type="hidden" name="address3" value="<?php echo set_value( 'address3' ); ?>" class="IM[<?php echo $cutomer_table; ?>@address3]" />
			</td>
		</tr>
		<tr>
			<th colspan="2">
				<input type="hidden" name="create_date" value="<?php echo $create_date; ?>" class="IM[<?php echo $cutomer_table; ?>@create_date]" />
				<input type="hidden" name="update_date" value="<?php echo $create_date; ?>" class="IM[<?php echo $cutomer_table; ?>@update_date]" />
				<input type="hidden" name="id" value="<?php echo set_value( 'id' ); ?>" class="IM[<?php echo $cutomer_table; ?>@id]" />
				<input type="submit" value="再入力" />
				<button class="_im_post">登録！</button>
			</th>
		</tr>
	</tbody>
  </table>
  </form>
  
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
