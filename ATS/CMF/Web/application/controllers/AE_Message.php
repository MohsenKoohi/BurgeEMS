<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Message extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_message',$this->selected_lang);
		$this->load->model(array("message_manager_model"));
	}

	public function message($message_id)
	{
		$message_id=(int)$message_id;
				
		$ret=$this->message_manager_model->get_admin_message($message_id);

		//bprint_r($ret['access']['added_departments']);
		//bprint_r($ret['access']['added_users']);
		
		if($ret)
		{
			if($this->input->post("post_type") === "add_reply_comment")
				return $this->add_reply_comment($message_id,$ret);

			if($this->input->post("post_type") === "set_participants")
				return $this->set_participants($message_id);

			$this->data['access']=$ret['access'];
			$this->data['message_info']=$ret['message'];
			$this->data['threads']=$ret['threads'];

			if($this->data['message_info'])
				$message_id=$this->data['message_info']['mi_message_id'];
			
			$this->data['departments']=$this->message_manager_model->get_departments();

			$this->data['departments_search_url']=get_link("admin_message_search_departments");
			$this->data['users_search_url']=get_link("admin_user_search");
			
		}
		else
		{
			$this->data['message_info']=NULL;	
		}

		$this->data['message_id']=$message_id;
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_message_details_link($message_id,TRUE));
		$this->data['header_title']=$this->lang->line("message")." ".$message_id;

		$this->send_admin_output("message_info");
	}

	public function index()
	{
		if($this->input->post("post_type") === "set_members")
			return $this->set_members();

		$this->set_messages();
		$this->set_groups();

		$this->data['message']=get_message();
		
		$this->data['lang_pages']=get_lang_pages(get_link("admin_message",TRUE));
		$this->data['header_title']=$this->lang->line("messages");

		$this->send_admin_output("message");

		return;	 
	}

	private function set_groups()
	{
		$this->data['additional_groups']=$this->message_manager_model->get_additional_groups();
		$this->data['group_url']=get_admin_message_group_link("gid");
		$this->data['selected_group_id']=0;

		if($this->input->get("group_id"))
		{

			$group_id=(int)$this->input->get("group_id");

			$this->data['selected_group_id']=$group_id;

			$this->data['page_url']=get_admin_message_group_link($group_id);
			$this->data['members']=$this->message_manager_model->get_group_members($group_id);
			$this->data['parents_search_url']=get_link('admin_customer_search');
		}

		return;
	}

	private function set_members()
	{
		$group_id=(int)$this->input->get("group_id");		
		$members=$this->input->post("members");
	
		$this->message_manager_model->set_group_members($group_id,$members);
	
		set_message($this->lang->line("changes_saved_successfully"));

		return redirect(get_admin_message_group_link($group_id));
	}

	private function set_messages()
	{
		return;
		$op_access=$this->data['op_access'];
		$departments=$this->message_manager_model->get_departments();
		$user_departments=array();
		foreach($departments as $id => $name)
			if($op_access['departments'][$name])
				$user_departments[]=$id;
		unset($departments);

		$access=array(
			"type"=>"user"
			,"id"=>$this->user_manager_model->get_user_info()->get_id()
			,"op_access"=>$op_access
			,"department_ids"=>$user_departments
		);

		$filters=array();

		$this->data['raw_page_url']=get_link("admin_message");
		
		$this->initialize_filters($filters,$access);
		
		$total=$this->message_manager_model->get_total_messages($filters,$access);
		if($total)
		{
			$per_page=20;
			$total_pages=ceil($total/$per_page);
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");
			if($page>$total_pages)
				$page=$total_pages;

			$start=($page-1)*$per_page;
			$filters['start']=$start;
			$filters['length']=$per_page;
			
			$this->data['messages']=$this->message_manager_model->get_messages($filters,$access);
			$this->process_messages_for_view();
			
			$end=$start+sizeof($this->data['messages'])-1;

			unset($filters['start']);
			unset($filters['length']);

			$this->data['messages_current_page']=$page;
			$this->data['messages_total_pages']=$total_pages;
			$this->data['messages_total']=$total;
			$this->data['messages_start']=$start+1;
			$this->data['messages_end']=$end+1;		
		}
		else
		{
			$this->data['messages_current_page']=0;
			$this->data['messages_total_pages']=0;
			$this->data['messages_total']=$total;
			$this->data['messages_start']=0;
			$this->data['messages_end']=0;
		}
		
		unset($filters['message_types']);

		$this->data['filters']=$filters;

		return;
	}

	//in this function we set limitations for messages based on 
	//filters the user has choosed
	//access for each message is considered based on $access in model 
	private function initialize_filters(&$filters,$access)
	{
		$fields=array(
			"start_date","end_date"
			,"status","verified","active"
			,"receiver_type","sender_type"
			,"sender_department","sender_user","sender_customer"
			,"receiver_department","receiver_user","receiver_customer"
		);

		foreach($fields as $field)
		{
			$filters[$field]=$this->input->get($field);
			$filters[$field]=persian_normalize($filters[$field]);		
		}
		
		$op_access=$access['op_access'];

		if(!$op_access['users'])
			$filters['active']="yes";

		$filters['message_types']=array();

		if(($filters['sender_type']!=="department") && 
			($filters['sender_type']!=="customer")   &&
			($filters['receiver_type']!=="department") && 
			($filters['receiver_type']!=="customer"))
				$this->set_user_message_types($filters);

		if(($filters['sender_type']!=="department") && 
			($filters['sender_type']!=="user") &&
			($filters['sender_type']!=="me") &&
			($filters['receiver_type']!=="department") && 
			($filters['receiver_type']!=="user") &&
			($filters['receiver_type']!=="me")
			)
				$this->set_customer_message_types($filters);
		
		if(($filters['sender_type']!=="user") &&			
			($filters['sender_type']!=="me") &&
			($filters['receiver_type']!=="user") && 
			($filters['receiver_type']!=="me") && 
			!(($filters['receiver_type']==="customer") && ($filters['sender_type']==="customer"))
			)
				$this->set_departments_message_types($filters);

		//bprint_r($op_access);
		//bprint_r($filters['message_types']);

		return;
	}
}