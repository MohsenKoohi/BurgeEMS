<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Message extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_message',$this->selected_lang);
		$this->load->model(array("message_manager_model"));
	}

	public function message($message_code)
	{
		$parts=explode("_",$message_code);
		if(sizeof($parts) != 2)
			return redirect(get_link("admin_message"));

		$filter=array();
		switch($parts[0][0])
		{
			case 's':
				$filter['part1_type']='student';
				break;
			case 't':
				$filter['part1_type']='teacher';
				break;
			case 'g':
				$filter['part1_type']='group';
				break;
			case 'p':
				$filter['part1_type']='parent';
				break;
		}
		$filter['part1_id']=substr($parts[0], 1);

		switch($parts[1][0])
		{
			case 's':
				$filter['part2_type']='student';
				break;
			case 'r':
				$filter['part2_type']='student_class';
				break;
			case 't':
				$filter['part2_type']='teacher';
				break;
			case 'g':
				$filter['part2_type']='group';
				break;
			case 'p':
				$filter['part2_type']='parent';
				break;
			case 'o':
				$filter['part2_type']='parent_class';
				break;
		}
		$filter['part2_id']=substr($parts[1], 1);

		$result=$this->message_manager_model->get_admin_message($filter);
		$this->data['messages']=$result;

		$this->load->model("class_manager_model");
		$this->data['class_names']=$this->class_manager_model->get_classes_names();

		$header=$this->get_message_header($result);
		$this->data['header']=$header;
		$this->data['header_title']=$header;
	
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_message_details_link($message_code,TRUE));

		$this->send_admin_output("message_info");
	}

	private function get_message_header($result)
	{
		$lmess=$result[sizeof($result)-1];

		$type=$lmess['message_sender_type'];
		if($type === "group")
			$sender=$this->lang->line("group_".$lmess['message_sender_id']."_name");
		if($type === "teacher")						
			$sender=$lmess['s_name']." (".$lmess['s_subject'].")";
		if(($type === "student") || ($type === "parent"))
			$sender=$lmess['s_name'];

		$type=$lmess['message_receiver_type'];
		if($type === "group")
			$receiver=$this->lang->line("group_".$lmess['message_receiver_id']."_name");
		if($type === "teacher")						
			$receiver=$lmess['r_name']." (".$lmess['r_subject'].")";
		if(($type === "student") || ($type === "parent"))
			$receiver=$lmess['r_name'];
		if($type === 'student_class')
			$receiver=$this->lang->line("students")." ".$this->data['class_names'][$lmess['message_receiver_id']];
		if($type === 'parent_class')
			$receiver=$this->lang->line("parents")." ".$this->data['class_names'][$lmess['message_receiver_id']];

		$header=$this->lang->line("messages_of")." ".$sender." ".$this->lang->line("and")." ".$receiver;

		return $header;
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

		$this->load->model("class_manager_model");
		$this->data['class_names']=$this->class_manager_model->get_classes_names();

		$this->data['raw_page_url']=get_link("admin_message");
		$filters=array();
		
		$total=$this->message_manager_model->get_admin_total_messages($filters);
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
			
			$this->data['messages']=$this->message_manager_model->get_admin_messages($filters);
			foreach($this->data['messages'] as &$mess)
				$mess['link']=$this->get_admin_message_link($mess);
			
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

	private function get_admin_message_link($mess)
	{
		$p1=$mess['message_sender_type'][0].$mess['message_sender_id'];
		$p2=$mess['message_receiver_type'][0].$mess['message_receiver_id'];
		if('student_class' === $mess['message_receiver_type'])
			$p2='r'.$mess['message_receiver_id'];
		if('parent_class' === $mess['message_receiver_type'])
			$p2='o'.$mess['message_receiver_id'];
	
		return get_admin_message_details_link($p1."_".$p2);
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