<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Message extends Burge_CMF_Controller {
	private $customer_info=NULL;

	function __construct()
	{
		parent::__construct();

		$this->load->model(array("customer_manager_model","message_manager_model"));
		
		$this->data['customer_logged_in']=$this->customer_manager_model->has_customer_logged_in();
		if(!$this->data['customer_logged_in'])
			return redirect(get_link("home_url"));

		$this->customer_info=$this->customer_manager_model->get_logged_customer_info();		

		$this->lang->load('ce_message',$this->selected_lang);		
	}

	public function details($message_id)
	{
		$message_id=(int)$message_id;

		$filter=array(
			"customer_type"=>$this->customer_info['customer_type']
			,"customer_id"=>$this->customer_info['customer_id']
			,"class_id"=>$this->customer_info['customer_class_id']
		);

		if(!$message_id)
			redirect(get_link("customer_message"));

		$this->data['message_id']=$message_id;
		
		$result=$this->message_manager_model->get_customer_message($message_id,$filter);
		if(!$result)
			redirect(get_link("customer_message"));

		$this->data['info']=$result;

		$this->data['captcha']=get_captcha();
		$this->load->model("class_manager_model");
		$this->data['class_names']=$this->class_manager_model->get_classes_names();

		$this->data['content']=$this->session->flashdata("content");
		
		$this->data['page_link']=get_customer_message_details_link($message_id);
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_customer_message_details_link($message_id,TRUE));

		$this->data['header_title']=$this->lang->line("message")." ".$message_id.$this->lang->line("header_separator").$this->data['header_title'];

		$this->send_customer_output("message_details");	
	}

	private function add_reply()
	{
		return; 
		if(verify_captcha($this->input->post("captcha")))
		{
			$props=array();
			$props['content']=$this->input->post('content');
			$props['subject']=$this->lang->line("reply_to").": ".$this->data['info']['message_subject'];
			
			if($props['content'])
			{
				persian_normalize($props);

				$customer_id=$this->customer_info['customer_id'];
				$props['sender_id']=$customer_id;
				$props['sender_type']="student";

				$props['receiver_type']=$this->data['info']['message_sender_type'];
				$props['receiver_id']=$this->data['info']['message_sender_id'];

				$this->message_manager_model->add_message($props);

				set_message($this->lang->line("reply_message_sent_successfully"));
				return redirect(get_link("customer_message"));
			}
			else
				set_message($this->lang->line("fill_all_fields"));
		}
		else
			set_message($this->lang->line("captcha_incorrect"));

		$this->session->set_flashdata("content",$content);
		
		return redirect(get_customer_message_details_link($this->data['message_id']));
	}

	public function message()
	{
		$this->data['message']=get_message();
		$this->data['page_link']=get_link("customer_message");
		
		$this->set_messages();

		$this->data['lang_pages']=get_lang_pages(get_link("customer_message",TRUE));

		$this->load->model("class_manager_model");
		$this->data['class_names']=$this->class_manager_model->get_classes_names();

		$this->data['header_title']=$this->lang->line("messages").$this->lang->line("header_separator").$this->data['header_title'];

		$this->send_customer_output("message_list");	
	}

	private function set_messages()
	{
		$filters=array(
			"customer_type"=>$this->customer_info['customer_type']
			,"customer_id"=>$this->customer_info['customer_id']
			,"class_id"=>$this->customer_info['customer_class_id']
		);

		$total=$this->message_manager_model->get_customer_total_messages($filters);

		if($total)
		{
			$per_page=10;
			$total_pages=ceil($total/$per_page);
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");
			if($page>$total_pages)
				$page=$total_pages;

			$start=($page-1)*$per_page;
			$filters['start']=$start;
			$filters['length']=$per_page;
			
			$this->data['messages']=$this->message_manager_model->get_customer_messages($filters);
			//bprint_r($this->data['messages']);
			
			$end=$start+sizeof($this->data['messages'])-1;

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

		return;
	}

	public function send()
	{
		if('student' === $this->customer_info['customer_type'])
			return $this->send_student();

		return;
	}

	private function send_student()
	{
		$class_id=$this->customer_info['customer_class_id'];
		
		$this->load->model("class_manager_model");
		$teachers=$this->class_manager_model->get_class_teachers($class_id);
		$this->teacher_ids=array();

		$receivers=array();
		foreach($teachers as $teacher)
			if($teacher['ct_teacher_id'])
			{
				$this->teacher_ids[]=$teacher['customer_id'];
				
				$value="t_".$teacher['customer_id'];
				$name=$teacher['customer_name']." (".$teacher['customer_subject'].")";
				$receivers[]=array("value"=>$value,"name"=>$name);
			}

		$groups=$this->message_manager_model->get_additional_groups();
		$this->group_ids=array();
		foreach($groups as $gid => $gname)
		{
			$this->group_ids[]=$gid;
			$value="g_".$gid;
			$name=$this->lang->line("group_".$gid."_name");
			$receivers[]=array("value"=>$value,"name"=>$name);
		}

		$this->data['receivers']=$receivers;

		$this->data['post_url']=get_link("customer_message_send");

		if($this->input->post())
			return $this->send_student_post();

		$this->data['message']=get_message();
		$this->data['captcha']=get_captcha();
		$this->data['lang_pages']=get_lang_pages(get_link('customer_message_send',TRUE));

		$this->data['subject']=$this->session->flashdata("message_subject");
		$this->data['content']=$this->session->flashdata("message_content");
		
		$this->data['header_title']=$this->lang->line("send_message").$this->lang->line("header_separator").$this->data['header_title'];
	
		$this->send_customer_output("message_send");
	}

	private function send_student_post()
	{
		if(verify_captcha($this->input->post("captcha")))
		{
			$fields=array("subject","content");
			$props=array();
			foreach($fields as $field)
				$props[$field]=$this->input->post($field);
			
			$receiver=$this->input->post("receiver");
			$receiver=explode("_", $receiver);
			$receiver_cond=
				(sizeof($receiver)==2) 
				&& (($receiver[0]=='t') || ($receiver[0]=='g'))
				&& (($receiver[0]=='g') || (in_array($receiver[1], $this->teacher_ids)) )
				&& (($receiver[0]=='t') || (in_array($receiver[1], $this->group_ids)) )
				;

			if($receiver_cond && $props['subject'] && $props['content'] )
			{
				persian_normalize($props);

				$customer_id=$this->customer_info['customer_id'];
				$props['sender_id']=$customer_id;
				$props['sender_type']="student";
				if($receiver[0]=='t')
					$props['receiver_type']="teacher";
				if($receiver[0]=='g')
					$props['receiver_type']="group";
				$props['receiver_id']=$receiver[1];

				$this->message_manager_model->add_message($props);

				set_message($this->lang->line("message_sent_successfully"));
				return redirect(get_link("customer_message"));
			}
			else
				set_message($this->lang->line("fill_all_fields"));
		}
		else
			set_message($this->lang->line("captcha_incorrect"));
		

		$this->session->set_flashdata("message_subject",$this->input->post("subject"));
		$this->session->set_flashdata("message_content",$this->input->post("content"));

		return redirect($this->data['post_url']);
	}

}