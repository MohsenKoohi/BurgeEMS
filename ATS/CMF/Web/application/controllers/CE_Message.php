<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Message extends Burge_CMF_Controller {
	private $customer_info=NULL;

	function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			"customer_manager_model"
			,"message_manager_model"
		));
		
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
			$class_code='r'.$this->customer_info['customer_class_id'];
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

			$this->load->model("class_manager_model");
			$classes=$this->class_manager_model->get_parent_class_ids($this->customer_info['customer_code']);
			if(!$classes)
				return FALSE;
			foreach($classes as $class_id)
			{
				$class_code='o'.$class_id;
				if(($parts[0] == $class_code) || ($parts[1] == $class_code))
					return TRUE;
			}
			
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
		
		$this->load->model("class_manager_model");
		$this->data['class_names']=$this->class_manager_model->get_classes_names();

		$this->set_messages();

		$this->data['lang_pages']=get_lang_pages(get_link("customer_message",TRUE));
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
		{
			$filters['parent_classes']=$this->class_manager_model->get_parent_class_ids($this->customer_info['customer_code']);
			if(!$filters['parent_classes'])
				return redirect("customer_dashboard");

			$filters['customer_groups']=$this->message_manager_model->get_customer_groups($this->customer_info['customer_id']);		
		}

		$total=$this->message_manager_model->get_customer_total_messages($filters);

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
		if('student_class' === $mess['message_receiver_type'])
			$p2='r'.$mess['message_receiver_id'];
		if('parent_class' === $mess['message_receiver_type'])
			$p2='o'.$mess['message_receiver_id'];
		return get_customer_message_details_link($p1."_".$p2);
	}

	public function search($name)
	{
		if ( 
				('teacher' !== $this->customer_info['customer_type'])
			  	&& ('parent' !== $this->customer_info['customer_type'])
			)
			return;
		$type=$this->input->get("type");
		if(("student" !== $type) && ("parent" !== $type))
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
				,"type"=>$type
				,"active"=>1
			);

			if($type=="student")
			{
				$filter["class_id_in"]=$classes;
				$results=$this->customer_manager_model->get_customers($filter);
			}

			if($type=="parent")
			{
				$filter["child_class_id_in"]=$classes;
				$results=$this->customer_manager_model->get_parents($filter);
			}
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
				,"type"=>"parent"
				,"active"=>1
			);

			$results=$this->customer_manager_model->get_parents($filter);
		}	

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

		$this->load->model("class_manager_model");
		$this->parent_classes=$this->class_manager_model->get_parent_class_ids($this->customer_info['customer_code']);
		if(!$this->parent_classes)
			return redirect(get_link("customer_dashboard"));

		$this->parent_groups=$this->message_manager_model->get_customer_groups($parent_id);
		$this->data['parent_groups']=$this->parent_groups;

		$this->sender_type="parent";
		$this->sender_id=$this->customer_info['customer_id'];
		$this->data['send_parent_url']=get_link("customer_message_send");

		if($this->parent_groups)
		{
			$this->data['send_group_url']=get_link("customer_message_send")."?sender=group";

			if($this->input->get("sender")=="group")
			{
				$this->sender_type="group";
				$this->sender_id=$this->parent_groups[0];	
			}
		}

		$this->data['sender_type']=$this->sender_type;

		if("parent" === $this->sender_type)
		{
			$teachers=$this->class_manager_model->get_class_teachers($this->parent_classes);
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

			$this->data['receivers']=$receivers;

			$this->data['post_url']=get_link("customer_message_send");

			if($this->input->post())
				return $this->send_parent_post();

			$this->data['message']=get_message();
			//$this->data['captcha']=get_captcha();
			$this->data['lang_pages']=get_lang_pages(get_link('customer_message_send',TRUE));

			$this->data['subject']=$this->session->flashdata("message_subject");
			$this->data['content']=$this->session->flashdata("message_content");
			
			$this->data['header_title']=$this->lang->line("send_message").$this->lang->line("header_separator").$this->data['header_title'];
		
			$this->send_customer_output("message_send_parent");

			return;
		}

		if("group" === $this->sender_type)
		{
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

			$this->data['post_url']=get_link("customer_message_send")."?sender=group";

			if($this->input->post())
				return $this->send_group_post();

			$this->data['message']=get_message();
			//$this->data['captcha']=get_captcha();
			$this->data['lang_pages']=get_lang_pages(get_link('customer_message_send',TRUE));

			$this->data['subject']=$this->session->flashdata("message_subject");
			$this->data['content']=$this->session->flashdata("message_content");
			$this->data['parents_search_url']=get_link('customer_message_search');
			$this->data['header_title']=$this->lang->line("send_message").$this->lang->line("header_separator").$this->data['header_title'];
		
			$this->send_customer_output("message_send_group");

			return;
		}
	}

	private function send_group_post()
	{
		if(1 || verify_captcha($this->input->post("captcha")))
		{
			$fields=array("subject","content");
			$props=array();
			foreach($fields as $field)
				$props[$field]=$this->input->post($field);

			if($props['subject'] && $props['content'] )
			{
				persian_normalize($props);
				$props['sender_id']=$this->sender_id;
				$props['sender_type']="group";
				$props['content'].=
					"\n\n".$this->customer_info['customer_name']
					."\n".$this->lang->line("group_".$this->sender_id."_name");

				$receiver_type=$this->input->post("receiver_type");

				if("parent_class" === $receiver_type)
				{
					$receiver_class_id=(int)$this->input->post("parent_class");
					
					if(!in_array($receiver_class_id,$this->class_ids))
					{
						set_message($this->lang->line("it_is_not_possible_to_send_message_to_this_class"));
						return redirect($this->data['post_url']);
					}

					$props['receiver_type']="parent_class";
					$props['receiver_id']=$receiver_class_id;

					$this->message_manager_model->add_message($props);

					set_message($this->lang->line("message_sent_successfully"));
					
					return redirect(get_link("customer_message"));
				}

				if("parent" === $receiver_type)
				{
					$parent_ids=explode(",",$this->input->post("parents"));
					$parent_ids=$this->class_manager_model->filter_parents_in_classes($parent_ids,$this->class_ids);

					if(!$parent_ids)
					{
						set_message($this->lang->line("it_is_not_possible_to_send_message_to_this_class"));
						return redirect($this->data['post_url']);
					}

					$props['receiver_type']="parent";		
					foreach($parent_ids as $pid)
					{
						$props['receiver_id']=$pid;

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

	private function send_parent_post()
	{
		if(1 || verify_captcha($this->input->post("captcha")))
		{
			$fields=array("subject","content");
			$props=array();
			foreach($fields as $field)
				$props[$field]=$this->input->post($field);
			
			$receiver=$this->input->post("receiver");
			$receiver=explode("_", $receiver);
			$receiver_cond=
				(sizeof($receiver)==2) 
				&& ($receiver[0]=='t')
				&& (in_array($receiver[1], $this->teacher_ids))
				;

			if($receiver_cond && $props['subject'] && $props['content'] )
			{
				persian_normalize($props);

				$customer_id=$this->customer_info['customer_id'];
				$props['sender_id']=$customer_id;
				$props['sender_type']="parent";
				$props['receiver_type']="teacher";
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
		//$this->data['captcha']=get_captcha();
		$this->data['lang_pages']=get_lang_pages(get_link('customer_message_send',TRUE));

		$this->data['subject']=$this->session->flashdata("message_subject");
		$this->data['content']=$this->session->flashdata("message_content");
		$this->data['students_search_url']=get_link('customer_message_search');
		$this->data['parents_search_url']=get_link('customer_message_search');
		$this->data['header_title']=$this->lang->line("send_message").$this->lang->line("header_separator").$this->data['header_title'];
	
		$this->send_customer_output("message_send_teacher");
	}

	private function send_teacher_post()
	{
		if(1 || verify_captcha($this->input->post("captcha")))
		{
			$fields=array("subject","content");
			$props=array();
			foreach($fields as $field)
				$props[$field]=$this->input->post($field);

			if($props['subject'] && $props['content'] )
			{
				persian_normalize($props);
				$teacher_id=$this->customer_info['customer_id'];
				$props['sender_id']=$teacher_id;
				$props['sender_type']="teacher";

				$receiver_type=$this->input->post("receiver_type");

				if("student_class" === $receiver_type)
				{
					$receiver_class_id=(int)$this->input->post("student_class");

					if(!in_array($receiver_class_id,$this->class_ids))
					{
						set_message($this->lang->line("it_is_not_possible_to_send_message_to_this_class"));
						return redirect(get_link("customer_message_send"));
					}

					$props['receiver_type']="student_class";
					$props['receiver_id']=$receiver_class_id;

					$this->message_manager_model->add_message($props);

					set_message($this->lang->line("message_sent_successfully"));
					
					return redirect(get_link("customer_message"));
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

					$props['receiver_type']="student";		
					foreach($student_ids as $sid)
					{
						$props['receiver_id']=$sid;

						$this->message_manager_model->add_message($props);
					}

					set_message($this->lang->line("messages_sent_successfully"));
					return redirect(get_link("customer_message"));
				}

				if("parent_class" === $receiver_type)
				{
					$receiver_class_id=(int)$this->input->post("parent_class");
					
					if(!in_array($receiver_class_id,$this->class_ids))
					{
						set_message($this->lang->line("it_is_not_possible_to_send_message_to_this_class"));
						return redirect(get_link("customer_message_send"));
					}

					$props['receiver_type']="parent_class";
					$props['receiver_id']=$receiver_class_id;

					$this->message_manager_model->add_message($props);

					set_message($this->lang->line("message_sent_successfully"));
					
					return redirect(get_link("customer_message"));
				}

				if("parent" === $receiver_type)
				{
					$parent_ids=explode(",",$this->input->post("parents"));
					$parent_ids=$this->class_manager_model->filter_parents_in_classes($parent_ids,$this->class_ids);

					if(!$parent_ids)
					{
						set_message($this->lang->line("it_is_not_possible_to_send_message_to_this_class"));
						return redirect(get_link("customer_message_send"));	
					}

					$props['receiver_type']="parent";		
					foreach($parent_ids as $pid)
					{
						$props['receiver_id']=$pid;

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

		$this->data['receivers']=$receivers;

		$this->data['post_url']=get_link("customer_message_send");

		if($this->input->post())
			return $this->send_student_post();

		$this->data['message']=get_message();
		//$this->data['captcha']=get_captcha();
		$this->data['lang_pages']=get_lang_pages(get_link('customer_message_send',TRUE));

		$this->data['subject']=$this->session->flashdata("message_subject");
		$this->data['content']=$this->session->flashdata("message_content");
		
		$this->data['header_title']=$this->lang->line("send_message").$this->lang->line("header_separator").$this->data['header_title'];
	
		$this->send_customer_output("message_send_student");
	}

	private function send_student_post()
	{
		if(1 || verify_captcha($this->input->post("captcha")))
		{
			$fields=array("subject","content");
			$props=array();
			foreach($fields as $field)
				$props[$field]=$this->input->post($field);
			
			$receiver=$this->input->post("receiver");
			$receiver=explode("_", $receiver);
			$receiver_cond=
				(sizeof($receiver)==2) 
				&& ($receiver[0]=='t')
				&& (in_array($receiver[1], $this->teacher_ids))
				;

			if($receiver_cond && $props['subject'] && $props['content'] )
			{
				persian_normalize($props);

				$customer_id=$this->customer_info['customer_id'];
				$props['sender_id']=$customer_id;
				$props['sender_type']="student";
				$props['receiver_type']="teacher";
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