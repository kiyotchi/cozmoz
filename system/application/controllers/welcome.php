<?php

class Welcome extends Controller {

	function Welcome()
	{
		parent::Controller();
	}

	function index()
	{
		$this->load->view('welcome_message');
	}
	
	function set()
	{
		echo '<pre>';
		echo prep_str(generate_google_analytics_mobile_tag($this->site_data->google_analytics));
		echo '</pre>';
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */