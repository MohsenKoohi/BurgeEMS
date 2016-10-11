<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Question_Collection extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_question_collection',$this->selected_lang);
		
		$this->load->model(array(
			"question_collector_model"
			)
		);

	}

	public function index()
	{
		if($this->input->post("post_type")==="set_prize_access")
			return $this->set_prize_access();

		//$this->data['rewards']=$this->reward_manager_model->get_all_rewards();
		
		$this->data['message']=get_message();

		$this->data['grades_count']=$this->question_collector_model->get_grades_count();
		$this->data['courses_count']=$this->question_collector_model->get_courses_count();

		$this->data['raw_page_url']=get_link("admin_question_collection");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_question_collection",TRUE));
		$this->data['header_title']=$this->lang->line("questions_collection");

		$this->send_admin_output("question_collection");

		return;	 
	}

	public function details($reward_id)
	{
		$reward_id=(int)$reward_id;

		$info=$this->reward_manager_model->get_reward_info($reward_id);
		if(!$info)
		{
			set_message($this->lang->line("reward_not_found"));
			return redirect(get_link("admin_reward"));		
		}

		$this->data['info']=$info;
		$this->data['students_rewards']=$this->reward_manager_model->get_reward_values($reward_id);

		$this->data['message']=get_message();

		$this->data['lang_pages']=get_lang_pages(get_admin_reward_details_link($reward_id,TRUE));
		$this->data['header_title']=$info['reward_subject'];

		$this->send_admin_output("reward_details");
	}

	private function set_prize_access()
	{
		$ids=$this->input->post("teachers-ids");

		$this->reward_manager_model->set_prize_teachers($ids);

		set_message($this->lang->line("modifications_have_been_done_successfully"));

		return redirect(get_link("admin_reward")."#prize-access");
	}
}