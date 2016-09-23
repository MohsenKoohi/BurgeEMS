<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Time extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_time',$this->selected_lang);
		$this->load->model("time_manager_model");

	}

	public function index()
	{
		if($this->input->post("post_type")==="add_time")
			return $this->add_time();

		$this->data['times']=$this->time_manager_model->get_all_times();

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("admin_time");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_time",TRUE));
		$this->data['header_title']=$this->lang->line("times");

		$this->send_admin_output("time");

		return;	 
	}	

	private function add_time()
	{
		$name=$this->input->post("name");

		$this->time_manager_model->add($name);

		set_message($this->lang->line("new_time_added_successfully"));

		return redirect(get_link("admin_time"));
	}
}