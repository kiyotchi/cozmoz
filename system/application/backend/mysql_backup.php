<?php

class Mysql_backup extends Backend
{
	protected $backend_name = 'データベースバックアップ';
	protected $description = '現在のデータベースのバックアップを行います。この処理を行うには、[system/logs/]に書き込み権限を与える必要があります。';
	
	public function run()
	{
		$CI =& get_instance();
		$CI->load->dbutil();
		
		$CI->load->helper('file_helper');
		
		$run_time = date('YmdHis');
		$log_path = BASEPATH . 'logs/';
		
		$param = array(
			'tables'		=> array(),
			'ignore'		=> array(),
			'format'		=> 'zip',
			'filename'		=> 'mysqlbackup_' . $run_time . '.sql',
			'add_drop'		=> TRUE,
			'add_insert'		=> TRUE,
			'newline'		=> "\n"
		);
		
		$backup =& $CI->dbutil->backup($param);
		
		if ( ! write_file($log_path . 'mysqlbackup_' . $run_time . '.zip', $backup))
		{
			return 'ファイルに書き込めませんでした。';
		}
		else
		{
			return 'バックアップファイルを作成しました。';
		}
	}
}
