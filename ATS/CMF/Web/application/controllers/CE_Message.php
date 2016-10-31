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

	private function has_access($parts)
	{
		if('student' === $this->customer_info['customer_type'])
		{
			$class_code='g-'.$this->customer_info['customer_class_id'];
			if(($parts[0] == $class_code) || ($parts[1] == $class_code))
				return TRUE;

			$student_code='s'.$this->customer_info['customer_id'];
			if(($parts[0] == $student_code) || ($parts[1] == $student_code))
				return TRUE;

			return FALSE;			
		}

		if('teacher' === $this->customer_info['customer_type'])
		{
			$teacher_code='t'.$this->customer_info['customer_id'];
			if(($parts[0] == $teacher_code) || ($parts[1] == $teacher_code))
				return TRUE;

			return FALSE;			
		}

		if('parent' === $this->customer_info['customer_type'])
		{
			$parent_code='p'.$this->customer_info['customer_id'];
			if(($parts[0] == $parent_code) || ($parts[1] == $parent_code))
				return TRUE;
			
			$groups=$this->message_manager_model->get_customer_groups($this->customer_info['customer_id']);		
			$group_code='g'.$groups[0];
			if(($parts[0] == $group_code) || ($parts[1] == $group_code))
				return TRUE;

			return FALSE;			
		}
	}

	public function details($message_code)
	{
		$parts=explode("_",$message_code);
		if(sizeof($parts) != 2)
			return redirect(get_link("customer_message"));

		if(!$this->has_access($parts))
			return redirect(get_link("customer_message"));		

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
			case 't':
				$filter['part2_type']='teacher';
				break;
			case 'g':
				$filter['part2_type']='group';
				break;
			case 'p':
				$filter['part2_type']='parent';
				break;
		}
		$filter['part2_id']=substr($parts[1], 1);

		$result=$this->message_manager_model->get_customer_message($filter);
		if(!$result)
			redirect(get_link("customer_message"));

		$this->data['messages']=$result;

		//$this->data['captcha']=get_captcha();
		$this->load->model("class_manager_model");
		$this->data['class_names']=$this->class_manager_model->get_classes_names();

		//$this->data['content']=$this->session->flashdata("content");
		
		$this->data['page_link']=get_customer_message_details_link($message_code);
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_customer_message_details_link($message_code,TRUE));
		
		$header=$this->get_message_header($result);
		$this->data['header']=$header;
		$this->data['header_title']=
			$header.$this->lang->line("header_separator")
			.$this->lang->line("messages").$this->lang->line("header_separator")
			.$this->data['header_title'];

		$this->send_customer_output("message_details");	
	}

	private function get_message_header($result)
	{
		$lmess=$result[sizeof($result)-1];

		$type=$lmess['message_sender_type'];
		if($type === "group")
		{
			if($lmess['message_sender_id'] > 0)
				$sender=$this->lang->line("group_".$lmess['message_sender_id']."_name");
			else
				$sender=$this->data['class_names'][-$lmess['message_sender_id']];
		}
		if($type === "teacher")						
			$sender=$lmess['s_name']." (".$lmess['s_subject'].")";
		if($type === "student" || $type === "parent")						
			$sender=$lmess['s_name'];

		$type=$lmess['message_receiver_type'];
		if($type === "group")
		{
			if($lmess['message_receiver_id'] > 0)
				$receiver=$this->lang->line("group_".$lmess['message_receiver_id']."_name");
			else
				$receiver=$this->data['class_names'][-$lmess['message_receiver_id']];
		}
		if($type === "teacher")						
			$receiver=$lmess['r_name']." (".$lmess['r_subject'].")";
		if($type === "student" || $type === "parent")						
			$receiver=$lmess['r_name'];

		$header=$this->lang->line("messages_of")." ".$sender." ".$this->lang->line("and")." ".$receiver;

		return $header;
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

		if('parent' === $this->customer_info['customer_type'])
			$filters['customer_groups']=$this->message_manager_model->get_customer_groups($this->customer_info['customer_id']);		

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
			foreach($this->data['messages'] as &$mess)
				$mess['link']=$this->get_message_link($mess);
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

	private function get_message_link($mess)
	{
		$p1=$mess['message_sender_type'][0].$mess['message_sender_id'];
		$p2=$mess['message_receiver_type'][0].$mess['message_receiver_id'];
		return get_customer_message_details_link($p1."_".$p2);
	}

	public function search($name)
	{
		if ( 
				('teacher' !== $this->customer_info['customer_type'])
			  	&& ('parent' !== $this->customer_info['customer_type'])
			)
			return;

		$max_count=5;
		$name=urldecode($name);
		$name=persian_normalize($name);

		if('teacher' === $this->customer_info['customer_type'])
		{
			$teacher_id=$this->customer_info['customer_id'];
			$this->load->model("class_manager_model");
			$classes=$this->class_manager_model->get_teacher_classes($teacher_id);
			if(!$classes)
				return;

			$filter=array(
				"name"=>$name
				,"start"=>0
				,"length"=>$max_count
				,"type"=>"student"
				,"class_id_in"=>$classes
			);
		}

		if('parent' === $this->customer_info['customer_type'])
		{
			$parent_id=$this->customer_info['customer_id'];
			$groups=$this->message_manager_model->get_customer_groups($parent_id);
			if(!$groups)
				return;

			$filter=array(
				"name"=>$name
				,"start"=>0
				,"length"=>$max_count
				,"type"=>"student"
			);
		}	

		$results=$this->customer_manager_model->get_customers($filter);

		$ret=array();

		foreach ($results as $res)	
			$ret[]=array(
				"id"=>$res['customer_id']
				,"name"=>$res['customer_name']
			);

		$this->output->set_content_type('application/json');
    	$this->output->set_output(json_encode($ret));

    	return;
	}

	public function send()
	{
		if('student' === $this->customer_info['customer_type'])
			return $this->send_student();

		if('teacher' === $this->customer_info['customer_type'])
			return $this->send_teacher();

		if('parent' === $this->customer_info['customer_type'])
			return $this->send_parent();

		return;
	}

	private function send_parent()
	{
		$parent_id=$this->customer_info['customer_id'];
	
		$groups=$this->message_manager_model->get_customer_groups($parent_id);		
		if(!$groups)
			return redirect(get_link("customer_dashboard"));
		$this->parent_groups=$groups;

		$this->load->model("class_manager_model");
		$classes=$this->class_manager_model->get_all_classes();
		$this->class_ids=array();

		$receivers=array();
		foreach($classes as $class)
		{
			$this->class_ids[]=$class['class_id'];
			
			$value=$class['class_id'];
			$name=$class['class_name'];
			$receivers[]=array("value"=>$value,"name"=>$name);
		}
		$this->data['receivers']=$receivers;

		$this->data['post_url']=get_link("customer_message_send");

		if($this->input->post())
			return $this->send_parent_post();

		$this->data['message']=get_message();
		$this->data['captcha']=get_captcha();
		$this->data['lang_pages']=get_lang_pages(get_link('customer_message_send',TRUE));

		$this->data['subject']=$this->session->flashdata("message_subject");
		$this->data['content']=$this->session->flashdata("message_content");
		$this->data['students_search_url']=get_link('customer_message_search');
		$this->data['header_title']=$this->lang->line("send_message").$this->lang->line("header_separator").$this->data['header_title'];
	
		$this->send_customer_output("message_send_parent");
	}

	private function send_parent_post()
	{
		if(verify_captcha($this->input->post("captcha")))
		{
			$fields=array("subject","content");
			$props=array();
			foreach($fields as $field)
				$props[$field]=$this->input->post($field);
			
			$receiver_type=$this->input->post("receiver_type");
			if("class" === $receiver_type)
			{
				$receiver_class_id=
				$receiver_type="group";
				$receiver_id=-(int)$this->input->post("class");
			}

			if("student" === $receiver_type)
			{
				$student_ids=explode(",",$this->input->post("students"));
				$student_ids=$this->class_manager_model->filter_students_in_classes($student_ids,$this->class_ids);

				if(!$student_ids)
				{
					set_message($this->lang->line("it_is_not_possible_to_send_message_to_this_class"));
					return redirect(get_link("customer_message_send"));	
				}

				$receiver_type="student";
				$receiver_ids=$student_ids;
			}
			
			if($props['subject'] && $props['content'] )
			{
				persian_normalize($props);

				//we assume each parent will only member of just one group not more ;)
				$group_id=$this->parent_groups[0];;
				$props['content'].=
					"\n\n".$this->customer_info['customer_name']
					."\n".$this->lang->line("group_".$group_id."_name");
				$props['sender_id']=$group_id;
				$props['sender_type']="group";
					
				if($receiver_type === 'group')
				{
					$props['receiver_type']="group";
					$props['receiver_id']=$receiver_id;

					$this->message_manager_model->add_message($props);

					set_message($this->lang->line("message_sent_successfully"));
					return redirect(get_link("customer_message"));
				}

				if($receiver_type === "student")
				{
					$props['receiver_type']="student";		
					foreach($receiver_ids as $rid)
					{
						$props['receiver_id']=$rid;

						$this->message_manager_model->add_message($props);
					}

					set_message($this->lang->line("messages_sent_successfully"));
					return redirect(get_link("customer_message"));
				}

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

	private function send_teacher()
	{
		$teacher_id=$this->customer_info['customer_id'];
		
		$this->load->model("class_manager_model");
		$classes=$this->class_manager_model->get_teacher_classes_with_names($teacher_id);
		if(!$classes)
			return redirect(get_link("customer_dashboard"));

		$this->class_ids=array();

		$receivers=array();
		foreach($classes as $class)
		{
			$this->class_ids[]=$class['class_id'];
			
			$value=$class['class_id'];
			$name=$class['class_name'];
			$receivers[]=array("value"=>$value,"name"=>$name);
		}
		$this->data['receivers']=$receivers;

		$this->data['post_url']=get_link("customer_message_send");

		if($this->input->post())
			return $this->send_teacher_post();

		$this->data['message']=get_message();
		$this->data['captcha']=get_captcha();
		$this->data['lang_pages']=get_lang_pages(get_link('customer_message_send',TRUE));

		$this->data['subject']=$this->session->flashdata("message_subject");
		$this->data['content']=$this->session->flashdata("message_content");
		$this->data['students_search_url']=get_link('customer_message_search');
		$this->data['header_title']=$this->lang->line("send_message").$this->lang->line("header_separator").$this->data['header_title'];
	
		$this->send_customer_output("message_send_teacher");
	}

	private function send_teacher_post()
	{
		if(verify_captcha($this->input->post("captcha")))
		{
			$fields=array("subject","content");
			$props=array();
			foreach($fields as $field)
				$props[$field]=$this->input->post($field);
			
			$receiver_type=$this->input->post("receiver_type");
			if("class" === $receiver_type)
			{
				$receiver_class_id=(int)$this->input->post("class");
				if(!in_array($receiver_class_id,$this->class_ids))
				{
					set_message($this->lang->line("it_is_not_possible_to_send_message_to_this_class"));
					return redirect(get_link("customer_message_send"));
				}

				$receiver_type="group";
				$receiver_id=-$receiver_class_id;
			}

			if("student" === $receiver_type)
			{
				$student_ids=explode(",",$this->input->post("students"));
				$student_ids=$this->class_manager_model->filter_students_in_classes($student_ids,$this->class_ids);

				if(!$student_ids)
				{
					set_message($this->lang->line("it_is_not_possible_to_send_message_to_this_class"));
					return redirect(get_link("customer_message_send"));	
				}

				$receiver_type="student";
				$receiver_ids=$student_ids;
			}
			
			if($props['subject'] && $props['content'] )
			{
				persian_normalize($props);
				$teacher_id=$this->customer_info['customer_id'];
				$props['sender_id']=$teacher_id;
				$props['sender_type']="teacher";
					
				if($receiver_type === 'group')
				{
					$props['receiver_type']="group";
					$props['receiver_id']=$receiver_id;

					$this->message_manager_model->add_message($props);

					set_message($this->lang->line("message_sent_successfully"));
					return redirect(get_link("customer_message"));
				}

				if($receiver_type === "student")
				{
					$props['receiver_type']="student";		
					foreach($receiver_ids as $rid)
					{
						$props['receiver_id']=$rid;

						$this->message_manager_model->add_message($props);
					}

					set_message($this->lang->line("messages_sent_successfully"));
					return redirect(get_link("customer_message"));
				}

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
	
		$this->send_customer_output("message_send_student");
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