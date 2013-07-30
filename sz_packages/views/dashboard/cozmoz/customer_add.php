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
  
  <form action="<?php echo site_url('dashboard/cozmoz/customer/confirm');?>" method="post">
  <table>
	<tbody>
		<tr>
			<th>会社名/屋号</th>
			<td>
				<?php
					echo form_input(array(
						'name' => 'company',
						'value' => set_value( 'company' ),
						'class' => 'IM[' . $cutomer_table . '@company]'
					));
				?>
				<?php echo form_dropdown( 'honorific', $hono, set_value( 'honorific' ), 'class="IM[' . $cutomer_table . '@honorific]"'); ?>
				<?php echo form_error('company', '<div class="error">', '</div>'); ?>
				<?php echo form_error('honorific', '<div class="error">', '</div>'); ?>
			</td>
		</tr>
		<tr>
			<th>お名前</th>
			<td>
				<?php
					echo form_input(array(
						'name' => 'name1',
						'value' => set_value( 'name1' ),
						'class' => 'IM[' . $cutomer_table . '@name1]'
					));
				?>
				 
				<?php
					echo form_input(array(
						'name' => 'name2',
						'value' => set_value( 'name2' ),
						'class' => 'IM[' . $cutomer_table . '@name2]'
					));
				?>
				<?php echo form_error('name1', '<div class="error">', '</div>'); ?>
				<?php echo form_error('name2', '<div class="error">', '</div>'); ?>
			</td>
		</tr>
		<tr>
			<th>ふりがな</th>
			<td>
				<?php
					echo form_input(array(
						'name' => 'kana1',
						'value' => set_value( 'kana1' ),
						'class' => 'IM[' . $cutomer_table . '@kana1]'
					));
				?>
				 
				<?php
					echo form_input(array(
						'name' => 'kana2',
						'value' => set_value( 'kana2' ),
						'class' => 'IM[' . $cutomer_table . '@kana2]'
					));
				?>
				<?php echo form_error('kana1', '<div class="error">', '</div>'); ?>
				<?php echo form_error('kana2', '<div class="error">', '</div>'); ?>
			</td>
		</tr>
		<tr>
			<th>TEL</th>
			<td>
				<?php
					echo form_input(array(
						'name' => 'tel',
						'value' => set_value( 'tel' ),
						'class' => 'IM[' . $cutomer_table . '@tel]'
					));
				?>
				<?php echo form_error('tel', '<div class="error">', '</div>'); ?>
			</td>
		</tr>
		<tr>
			<th>FAX</th>
			<td>
				<?php
					echo form_input(array(
						'name' => 'fax',
						'value' => set_value( 'fax' ),
						'class' => 'IM[' . $cutomer_table . '@fax]'
					));
				?>
				<?php echo form_error('fax', '<div class="error">', '</div>'); ?>
			</td>
		</tr>
		<tr>
			<th>メールアドレス</th>
			<td>
				<?php
					echo form_input(array(
						'name' => 'mailaddress',
						'value' => set_value( 'mailaddress' ),
						'class' => 'IM[' . $cutomer_table . '@mailaddress]'
					));
				?>
				<?php echo form_error('mailaddress', '<div class="error">', '</div>'); ?>
			</td>
		</tr>
		<tr>
			<th>住所</th>
			<td>
				<?php
					echo form_input(array(
						'name' => 'zip',
						'value' => set_value( 'zip' ),
						'class' => 'IM[' . $cutomer_table . '@zip]'
					));
				?><br />
				<?php
					echo form_dropdown(
							'pref',
							$pref,
							set_value( 'pref' ),
							'class=' . '"IM[' . $cutomer_table . '@zip]"'
					);
				?><br />
				<?php
					echo form_input(array(
						'name' => 'address1',
						'value' => set_value( 'address1' ),
						'class' => 'IM[' . $cutomer_table . '@address1]'
					));
				?><br />
				<?php
					echo form_input(array(
						'name' => 'address2',
						'value' => set_value( 'address2' ),
						'class' => 'IM[' . $cutomer_table . '@address2]'
					));
				?><br />
				<?php
					echo form_input(array(
						'name' => 'address3',
						'value' => set_value( 'address3' ),
						'class' => 'IM[' . $cutomer_table . '@address3]'
					));
				?>
				<?php echo form_error('zip', '<div class="error">', '</div>'); ?>
				<?php echo form_error('pref', '<div class="error">', '</div>'); ?>
				<?php echo form_error('address1', '<div class="error">', '</div>'); ?>
				<?php echo form_error('address2', '<div class="error">', '</div>'); ?>
				<?php echo form_error('address3', '<div class="error">', '</div>'); ?>
			</td>
		</tr>
		<tr>
			<th colspan="2">
				<input type="submit" value="確認画面へ" />
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
