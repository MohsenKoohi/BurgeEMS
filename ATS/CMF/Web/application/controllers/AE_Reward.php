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

		
		$this->data['teachers']=$this->reward_manager_model->get_prize_teachers();
		$this->data['classes']=$this->class_manager_model->get_all_classes();

		$this->set_rewareds();
		
		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("admin_reward");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_reward",TRUE));
		$this->data['header_title']=$this->lang->line("rewards");

		$this->send_admin_output("reward");

		return;	 
	}

	private function set_rewareds()
	{

		$items_per_page=20;
		$page=1;
		if($this->input->get("page"))
			$page=(int)$this->input->get("page");

		$filter=array();

		$pfnames=array("teacher_id","class_id","start_date","end_date","is_prize","subject");
		foreach($pfnames as $pfname)
			if($this->input->get($pfname)!==NULL)
				$filter[$pfname]=$this->input->get($pfname);	

		$total=$this->reward_manager_model->get_total_rewards($filter);
		$this->data['total_count']=$total;
		$this->data['total_pages']=ceil($total/$items_per_page);
		if($total)
		{
			if($page > $this->data['total_pages'])
				$page=$this->data['total_pages'];
			if($page<1)
				$page=1;
			$this->data['current_page']=$page;
			
			$start=($page-1)*$items_per_page;
			$filter['start']=$start;
			$filter['length']=$items_per_page;

			$end=$start+$items_per_page-1;
			if($end>($total-1))
				$end=$total-1;
			$this->data['results_start']=$start+1;
			$this->data['results_end']=$end+1;		
	
			$filter['order_by']="reward_id DESC";

			$this->data['rewards']=$this->reward_manager_model->get_rewards($filter);

			unset($filter['start'],$filter['length'],$filter['order_by']);
		}
		else
		{
			$this->data['results_start']=0;
			$this->data['results_end']=0;
			$this->data['rewards']=array();
		}

		$this->data['filter']=$filter;

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