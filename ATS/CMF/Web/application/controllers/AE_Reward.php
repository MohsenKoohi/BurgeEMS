<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Reward extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_reward',$this->selected_lang);
		
		$this->load->model(array(
			"reward_manager_model"
			,"class_manager_model"
			,"customer_manager_model"
			)
		);

	}

	public function index()
	{
		if($this->input->post("post_type")==="set_prize_access")
			return $this->set_prize_access();

		$this->data['rewards']=$this->reward_manager_model->get_all_rewards();
		$this->data['teachers']=$this->reward_manager_model->get_prize_teachers();
		
		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("admin_reward");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_reward",TRUE));
		$this->data['header_title']=$this->lang->line("rewards");

		$this->send_admin_output("reward");

		return;	 
	}

	public function details($class_id)
	{
		$class_id=(int)$class_id;

		$info=$this->class_manager_model->get_class($class_id);
		if(!$info)
		{
			set_message($this->lang->line("class_not_found"));
			return redirect(get_link("admin_class"));		
		}

		if($this->input->post("post_type")==="students_resort")
			return $this->students_resort($class_id);

		if($this->input->post("post_type")==="set_teachers")
			return $this->set_teachers($class_id);		

		if($this->input->post("post_type")==="delete_class")
			return $this->delete_class($class_id);

		if($this->input->post("post_type")==="set_curriculum")
			return $this->set_class_curriculum($class_id);

		$this->data['info']=$info;
		$this->data['teachers']=$this->class_manager_model->get_teachers($class_id);
		$this->data['students']=$this->class_manager_model->get_students($class_id);
		$this->data['curriculum_hours']=$this->class_manager_model->get_curriculum_hours();
		$this->data['curriculum']=$this->class_manager_model->get_class_curriculum($class_id);

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_admin_class_details_link($class_id);
		$this->data['lang_pages']=get_lang_pages(get_admin_class_details_link($class_id,TRUE));
		$this->data['header_title']=$info['class_name'];

		$this->send_admin_output("class_details");
	}

	private function set_prize_access()
	{
		$ids=$this->input->post("teachers-ids");

		$this->reward_manager_model->set_prize_teachers($ids);

		set_message($this->lang->line("modifications_have_been_done_successfully"));

		return redirect(get_link("admin_reward")."#prize-access");
	}
}