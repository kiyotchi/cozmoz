<?php
/* =============================================================================
 * IMライブラリ
 * ========================================================================== */
class im_require_lib
{
	//コントローラー
	private $ci;
	
	// ----------------------------------------------------
	
	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->library( 'session' );
	}
	
	// ----------------------------------------------------
	
	/**
	 * URIをハッシュ取得
	 * @param string $url
	 * @return string
	 */
	public function get_uri_hash( $uri = null )
	{
		if(is_null($uri))
		{
			return 'cozmoz_' . md5( $this->ci->uri->uri_string() );
		}
		return 'cozmoz_' . md5($uri);
	}
	
	// ----------------------------------------------------
	
	/**
	 * データベース定義を保存
	 * @param array $def
	 * @return string
	 */
	public function save_def( $def )
	{
		$hash = $this->get_uri_hash();
		$this->ci->session->set_userdata( $hash, $def );
		return $hash;
	}
	
	// ----------------------------------------------------
	
	/**
	 * データベース定義を取得
	 * @return string $hash
	 */
	public function get_def( $hash )
	{
		return $this->ci->session->userdata( $hash );
	}
	
	// ----------------------------------------------------
}