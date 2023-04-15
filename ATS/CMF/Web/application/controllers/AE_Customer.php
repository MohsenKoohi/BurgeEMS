<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Customer extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model("customer_manager_model");
		$this->load->model("class_manager_model");
	}

	public function search($search_value)
	{
		$max_count=5;
		$filter=array(
			"start"=>0
			,"length"=>$max_count
		);

		$search_for=$this->input->get("for");
		$search_value=urldecode($search_value);
		$search_value=persian_normalize($search_value);
		
		if('name' != $search_for && 'code' != $search_for)
			return;
		$filter[$search_for]=$search_value;

		if($this->input->get("type"))
			$filter['type']=$this->input->get("type");
		if($this->input->get("active"))
			$filter['active']=$this->input->get("active");

		$results=$this->customer_manager_model->get_customers($filter);

		$ret=array();

		foreach ($results as $res)	
			$ret[]=array(
				"id"=>$res['customer_id']
				,"name"=>$res['customer_name']
				,"code"=>$res['customer_code']
			);

		$this->output->set_content_type('application/json');
    	$this->output->set_output(json_encode($ret));

    	return;
	}
	
	public function index()
	{
		$this->lang->load('ae_customer',$this->selected_lang);
		
		$this->data['message']=get_message();
		
		if($this->input->post())
		{
			$this->lang->load('error',$this->selected_lang);

			if("add_customer" === $this->input->post("post_type"))
				return $this->add_customer();

			if("sort_customers" === $this->input->post("post_type"))
				$this->sort();
		}
		
		$this->set_data_customers();
		$this->data['classes']=$this->class_manager_model->get_all_classes();
		$this->data['password_page_url']=get_link("admin_customer_password");
		$this->data['raw_page_url']=get_link("admin_customer");
		$this->data['provinces']=$this->customer_manager_model->get_provinces($this->selected_lang);
		$this->data['cities']=$this->customer_manager_model->get_cities($this->selected_lang);

		$page_raw_lang_url=get_link("admin_customer",TRUE);
		$this->data['lang_pages']=get_lang_pages($page_raw_lang_url);

		$this->data['header_title']=$this->lang->line("customers");
		$this->data['customer_types']=$this->customer_manager_model->get_customer_types();

		$this->send_admin_output("customer");

		return;	 
	}

	private function sort()
	{
		$ids=$this->input->post("customers-ids");

		if($ids)
			$this->customer_manager_model->sort($ids);

		$this->data['message']=$this->lang->line("new_sorting_saved_successfully");

		return;
	}


	public function password()
	{
		$this->load->model("constant_manager_model");
		if(!$this->constant_manager_model->get("allow_customer_new_password"))
		{
			echo "No Access";
			return;
		}

		$filter=array();
		$pfnames=array("id","name","type","email","code","province","city","address","phone_mobile","class_id");
		foreach($pfnames as $pfname)
			if($this->input->get($pfname))
				$filter[$pfname]=$this->input->get($pfname);	

		if(!$filter)
			return;

		$filter['active']=1;
		$filter['order_by']="customer_order ASC";

		$customers=$this->customer_manager_model->get_customers($filter);
		if(sizeof($customers)>1)
			if(!$this->constant_manager_model->get("allow_customer_bulk_new_password"))
			{
				echo "No Access";
				return;
			}

		foreach($customers as $index=>&$customer)
		{
			$pass=NULL;
			if($customer['customer_code'])
			{
				$pass=$this->customer_manager_model->set_new_password_by_id($customer['customer_id']);
				if($pass)
					$customer['customer_pass']=$pass;
			}

			if(!$pass)
				unset($customers[$index]);
		}

		if(isset($filter['class_id']))
		{
			$parents=$this->customer_manager_model->get_parents(array(
				"child_class_id_in"	=> array($filter['class_id'])
				,"active"				=> 1
			));
			foreach($parents as $index=>&$customer)
			{
				$pass=NULL;
				if($customer['customer_code'])
				{
					$pass=$this->customer_manager_model->set_new_password_by_id($customer['customer_id']);
					if($pass)
						$customer['customer_pass']=$pass;
				}

				if($pass)
					$customers[]=$customer;
			}
		}

		$this->lang->load('ae_customer',$this->selected_lang);
		foreach($this->lang->language as $index => $val)
			$this->data[$index."_text"]=$val;

		$this->data['url']=get_link("customer_login");
		$this->data['customers']=$customers;
		$this->load->library('parser');
		$this->parser->parse($this->get_admin_view_file("customer_pass"),$this->data);
		
		return;
	}

	private function set_data_customers()
	{
		$items_per_page=30;
		$page=1;
		if($this->input->get("page"))
			$page=(int)$this->input->get("page");

		$filter=array();

		$pfnames=array("name","type","email","code","province","city","address","phone_mobile","active","class_id");
		foreach($pfnames as $pfname)
			if($this->input->get($pfname)!==NULL)
				$filter[$pfname]=$this->input->get($pfname);	

		$total=$this->customer_manager_model->get_total_customers($filter);
		$this->data['customers_total']=$total;
		$this->data['customers_total_pages']=ceil($total/$items_per_page);
		if($total)
		{
			if($page > $this->data['customers_total_pages'])
				$page=$this->data['customers_total_pages'];
			if($page<1)
				$page=1;
			$this->data['customers_current_page']=$page;
			
			$start=($page-1)*$items_per_page;
			$filter['start']=$start;
			$filter['length']=$items_per_page;

			$end=$start+$items_per_page-1;
			if($end>($total-1))
				$end=$total-1;
			$this->data['customers_start']=$start+1;
			$this->data['customers_end']=$end+1;		
	
			$filter['order_by']="customer_order ASC";

			$this->data['customers_info']=$this->customer_manager_model->get_customers($filter);

			unset($filter['start'],$filter['length'],$filter['order_by']);
		}
		else
		{
			$this->data['customers_start']=0;
			$this->data['customers_end']=0;
			$this->data['customers_info']=array();
		}

		$this->data['filter']=$filter;

		return;
	}

	private function add_customer()
	{
		$customer_name=$this->input->post("customer_name");
		$customer_type=$this->input->post("customer_type");
		$customer_phone=$this->input->post("customer_phone");
		$customer_mobile=$this->input->post("customer_mobile");
		$customer_subject=$this->input->post("customer_subject");
		$customer_class_id=$this->input->post("customer_class_id");
		$customer_code=$this->input->post("customer_code");
		$customer_father_code=$this->input->post("customer_father_code");
		$customer_mother_code=$this->input->post("customer_mother_code");

		$customer_birthday=persian_normalize_word($this->input->post("customer_birthday"));
		validate_persian_date($customer_birthday);
		
		$desc=$this->input->post("desc");

		if(!$customer_type || !$customer_name)
			set_message($this->lang->line("fill_all_fields"));
		else
		{
			$res=$this->customer_manager_model->add_customer(array(
				"customer_name"=>$customer_name
				,"customer_type"=>$customer_type
				,"customer_phone"=>$customer_phone
				,"customer_mobile"=>$customer_mobile
				,"customer_subject"=>$customer_subject
				,"customer_class_id"=>$customer_class_id
				,"customer_code"=>$customer_code
				,"customer_father_code"=>$customer_father_code
				,"customer_mother_code"=>$customer_mother_code
				,"customer_birthday"=>$customer_birthday

				), $desc);

			if($res)
				set_message($this->lang->line("added_successfully"));
			else
				set_message($this->lang->line("code_email_must_be_unique"));
		}

		return redirect(get_link("admin_customer"));
	}

	public function customer_details($customer_id,$task_id=0)
	{
		$customer_id=(int)$customer_id;
		$task_id=(int)$task_id;

		$this->data['customer_id']=$customer_id;
		$this->data['task_id']=$task_id;
		$this->data['messages_address']=get_link("admin_message")."?customer_id=".$customer_id;

		$this->lang->load('ae_customer_details',$this->selected_lang);

		$this->data['customer_info']=$this->customer_manager_model->get_customer_info($customer_id);		

		if($this->input->post())
		{
			$this->lang->load('error',$this->selected_lang);

			if("customer_properties" === $this->input->post("post_type"))
				return $this->save_customer_new_properties($customer_id,$task_id);

			if("set_password" === $this->input->post("post_type"))
				return $this->set_customer_password($customer_id,$task_id);

			if("customer_login" === $this->input->post("post_type"))
				return $this->user_login_as_customer($customer_id);
		}

		//check customer events for events tab
		$this->get_and_set_events($customer_id,$task_id);

		//submit task exec results and retreive all fields for tasks tab
		if($task_id)
			$this->task_exec($customer_id,$task_id);

		//get all executed tasks with current one
		$this->load->model("task_exec_manager_model");
		$extasks=$this->task_exec_manager_model->get_executed_tasks($customer_id);
		$customer_tasks=array();
		foreach($extasks as $et)
			$customer_tasks[$et['task_id']]=$et['task_name'];
		
		if($task_id && !isset($customer_tasks[$task_id]) && isset($this->data['task_info']))
			$customer_tasks[$this->data['task_info']['task_id']]=$this->data['task_info']['task_name'];

		$this->data['customer_tasks']=$customer_tasks;

		$this->get_logs($customer_id);

		$this->data['message']=get_message();

		$this->load->model("constant_manager_model");
		$this->data['our_address']=$this->constant_manager_model->get("address_".$this->language->get());
		
		if(NULL == $this->data['customer_info'])
			$this->data['message']=$this->lang->line("customer_not_found");

		if($this->data['customer_info'])
		{
			$this->load->model("reward_manager_model");

			if('student' === $this->data['customer_info']['customer_type'])
			{
				$this->data['rewards']=$this->reward_manager_model->get_student_rewards($customer_id);
				$this->data['total_rewards']=$this->reward_manager_model->get_student_total_rewards($customer_id);
			}

			if('teacher' === $this->data['customer_info']['customer_type'])
			{
				$this->data['rewards_link']=get_link("admin_reward")."?teacher_id=".$customer_id;
			}

		}
		
		$this->data['parents_search_url']=get_link("admin_customer_search");
		$this->data['new_pass_link']=get_link("admin_customer_password")."?id=".$customer_id;
		$this->data['raw_page_url']=get_admin_customer_details_link($customer_id,$task_id);
		$this->data['lang_pages']=get_lang_pages(get_admin_customer_details_link($customer_id,$task_id,NULL,TRUE));

		$this->data['classes']=$this->class_manager_model->get_all_classes();
		$this->data['customer_types']=$this->customer_manager_model->get_customer_types();		
		$this->data['provinces']=$this->customer_manager_model->get_provinces($this->selected_lang);
		$this->data['cities']=$this->customer_manager_model->get_cities($this->selected_lang);

		$this->data['header_title']=$this->lang->line("customer_details");
		if($this->data['customer_info'])
			$this->data['header_title'].=" | ".$this->data['customer_info']['customer_name'];
		

		$this->send_admin_output("customer_details");


		return;	 		
	}

	private function user_login_as_customer($customer_id)
	{
		if($this->customer_manager_model->user_login_as_customer($customer_id))
			return redirect(get_link("customer_dashboard"));

		set_message($this->lang->line("customer_code_has_not_been_specified_or_inactive_or_no_access"));

		redirect(get_admin_customer_details_link($customer_id));
	}

	private function get_and_set_events($customer_id,$task_id)
	{
		$evtypes=$this->customer_manager_model->get_customer_event_types();

		if($this->input->post() && ("set_events" === $this->input->post("post_type")))
		{
			$events=array();	
			foreach($evtypes as $et)
				if($this->input->post($et) === "on")
					$events[]=$et;

			$this->customer_manager_model->set_customer_events($customer_id,$events);

			set_message($this->lang->line("events_set_successfully"));

			return redirect(get_admin_customer_details_link($customer_id,$task_id,"events"));
		}

		$this->data['customer_event_types']=$evtypes;

		$cevs=$this->customer_manager_model->get_customer_events($customer_id);
		$cus_events=array();
		foreach ($cevs as $event)
			$cus_events[$event['ce_event_type']]=$event['ce_timestamp'];
		
		$this->data['customer_events']=$cus_events;

		return;
	}

	private function get_logs($customer_id)
	{
		$this->data['log_types']=$this->customer_manager_model->get_customer_log_types();
		
		$filter=array();
		$log_filter=array();

		if($this->input->get('log_type'))
		{
			//this var is sent to view
			$filter['log_type']=$this->input->get('log_type');

			//this var is sent to retreive_customer_logs
			$log_filter['log_types']=array($this->input->get('log_type'));
		}

		$this->data['filter']=$filter;
		
		$logs_pp=10;
		$page=1;
		if($this->input->get("page"))
			$page=(int)$this->input->get("page");

		$start=($page-1)*$logs_pp;
		$log_filter['start']=$start;
		$log_filter['length']=$logs_pp;
		
		$log_res=$this->customer_manager_model->get_customer_logs($customer_id,$log_filter);
		
		$total=$log_res['total'];
		$this->data['customer_logs']=$log_res['results'];
		$end=$start+sizeof($this->data['customer_logs'])-1;

		$this->data['logs_current_page']=$page;
		$this->data['logs_total_pages']=ceil($total/$logs_pp);
		$this->data['logs_total']=$total;
		if($total)
		{
			$this->data['logs_start']=$start+1;
			$this->data['logs_end']=$end+1;		
		}
		else
		{
			$this->data['logs_start']=0;
			$this->data['logs_end']=0;
		}

		return;
	}

	private function save_customer_new_properties($customer_id,$task_id)
	{
		$customer_birthday=persian_normalize_word($this->input->post("customer_birthday"));
		validate_persian_date($customer_birthday);

		$args=array(
			"customer_name"				=>$this->input->post("customer_name")
			,"customer_type"				=>$this->input->post("customer_type")
			,"customer_email"				=>$this->input->post("customer_email")
			,"customer_code"				=>$this->input->post("customer_code")
			,"customer_father_code"		=>$this->input->post("customer_father_code")
			,"customer_mother_code"		=>$this->input->post("customer_mother_code")
			,"customer_province"			=>$this->input->post("customer_province")
			,"customer_city"				=>$this->input->post("customer_city")
			,"customer_address"			=>$this->input->post("customer_address")
			,"customer_phone"				=>$this->input->post("customer_phone")
			,"customer_mobile"			=>$this->input->post("customer_mobile")
			,"customer_subject"			=>$this->input->post("customer_subject")
			,"customer_class_id"			=>$this->input->post("customer_class_id")
			,"customer_birthday"			=>$customer_birthday
			,"customer_active"			=>($this->input->post("customer_active")==="on")
		);

		if( $this->input->post("customer_type") == 'student' )
			if($this->input->post("customer_active")!=="on")
				$this->customer_manager_model->deactivate_student($customer_id);
		
		if( $this->input->post("customer_type") != 'student' )
			$args['customer_class_id'] = NULL;

		if(isset($_FILES['customer_image']) && $_FILES['customer_image']['name'])
		{
			$file_name=$_FILES['customer_image']['name'];
			if(strtolower(pathinfo($file_name, PATHINFO_EXTENSION))=="jpg")
			{
				$info=$this->customer_manager_model->get_customer_info($customer_id);
				@unlink(get_customer_image_path($customer_id,$info['customer_image_hash']));

				$temp_path=$_FILES['customer_image']['tmp_name'];
				$hash=get_random_word(5,TRUE);
				$new_path=get_customer_image_path($customer_id,$hash);
				@move_uploaded_file($temp_path, $new_path);

				$args['customer_image_hash']=$hash;
			}
		}

		$desc=$this->input->post("desc");

		$result=$this->customer_manager_model->set_customer_properties($customer_id,$args,$desc);

		switch($result)
		{
			case 0:
				set_message($this->lang->line("saved_successfully"));
				break;

			case -1:
				set_message($this->lang->line("customer_name_is_null"));
				break;

			case -2:
				set_message($this->lang->line("customer_email_is_repetitive"));
				break;

			case -3:
				set_message($this->lang->line("customer_email_cant_be_deleted"));
				break;

			case -4:
				set_message($this->lang->line("customer_code_is_repetitive"));
				break;
		}

		redirect(get_admin_customer_details_link($customer_id,$task_id));

		return;
	}

	private function set_customer_password($customer_id,$task_id)
	{
		$pass=trim($this->input->post("customer_password"));
		if(!$pass)
			return redirect(get_admin_customer_details_link($customer_id,$task_id));

		$result=$this->customer_manager_model->set_new_password_by_id($customer_id,$pass);
		$email=$this->data['customer_info']['customer_email'];

		if($result && $email)
		{
			$this->lang->load('email_lang',$this->selected_lang);		
			$this->lang->load('ae_customer_details_lang',$this->selected_lang);		

			$subject=$this->lang->line("new_password");
			$subject=$subject.$this->lang->line("header_separator").$this->lang->line("main_name");
			$content=str_replace('$pass',$pass, $this->lang->line("new_password_email_content"));
			
			$message=str_replace(
				array('$content','$slogan','$response_to'),
				array($content,$this->lang->line("slogan"),"")
				,$this->lang->line("email_template")
			);

			burge_cmf_send_mail($email,$subject,$message);
		}

		if($result)
			set_message($this->lang->line("new_password_was_successfully_set"));
		
		redirect(get_admin_customer_details_link($customer_id,$task_id)."#");

		return;
	}

	private function task_exec($customer_id, $task_id)
	{
		//check if this customer has this task to execute
		//we assume yes, because it has been redirected here from task_exec page
		//but you can be sure for next versions
		//or if a customer has a task, it is very good to do that task
		//without redirecting from its page
		//its not so complicated, ...


		//check if this user can do this task
		$user_id=$this->user->get_id();
		$this->load->model("task_manager_model");
		$can_exec=$this->task_manager_model->check_user_can_execute_task($user_id,$task_id);

		$this->data['user_can_exec']=($can_exec > 0);
		$this->data['user_is_manager']=(2 === $can_exec);

		$this->load->model("task_exec_manager_model");
		
		if((2 === $can_exec) && ($this->input->post("post_type") === "manager_note"))
		{
			$note=$this->input->post("manager_note");
			$note.="\n".$this->user->get_name()." - ".$this->user->get_code();
			$status=$this->input->post("manager_task_status");

			$props=array(
				"te_status"							=>$status
				,"te_last_exec_manager_note"	=>$note
			);

			if("changing" === $status)
			{
				$next_exec=persian_normalize_word($this->input->post("manager_remind_in"));
				if($next_exec)
					$props['te_next_exec']=$this->get_next_exec_date($next_exec);
			}

			$this->task_exec_manager_model->set_manager_note($customer_id, $task_id, $props);

			set_message($this->lang->line("note_saved_successfully"));
			
			redirect(get_admin_customer_details_link($customer_id,$task_id,"tasks"));
		}

		if(($can_exec > 0) && ($this->input->post("post_type") === "task_exec"))
		{
			$date_function=DATE_FUNCTION;

			$timestamp=$date_function("Y-m-d H:i:s");
			$status=$this->input->post("task_status");
			$next_exec=0;
			if("changing" === $status)
			{
				$next_exec=persian_normalize_word($this->input->post("task_exec_remind_in"));
				$next_exec=$this->get_next_exec_date($next_exec);
			}

			$result=$this->input->post("task_exec_result");

			if(isset($_FILES['task_exec_file']) && $_FILES['task_exec_file']['name'])
			{
				$exec_count=1+$this->task_exec_manager_model->get_task_exec_count($customer_id,$task_id);
				$file_name=$_FILES['task_exec_file']['name'];
				$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
				$temp_path=$_FILES['task_exec_file']['tmp_name'];
				
				$file=$this->customer_manager_model->add_and_move_customer_task_exec_file(array(
					"customer_id"		=>$customer_id
					,"task_id"			=>$task_id
					,"exec_count"		=>$exec_count
					,"file_extension"	=>$file_extension
					,"temp_path"		=>$temp_path
				));
				
			}
			else
				$file="";
			$requires_manager_note=("on" === $this->input->post("task_exec_requires_manager_note"));

			$props=array(
				"te_status"										=>$status
				,"te_next_exec"								=>$next_exec
				,"te_last_exec_user_id"						=>$user_id
				,"te_last_exec_timestamp"					=>$timestamp
				,"te_last_exec_result"						=>$result
				,"te_last_exec_result_file_name"			=>$file
				,"te_last_exec_requires_manager_note"	=>$requires_manager_note
			);

			$this->task_exec_manager_model->update_task_exec_info($customer_id, $task_id, $props);

			set_message($this->lang->line("task_saved_successfully"));
			
			redirect(get_admin_customer_details_link($customer_id,$task_id,"tasks"));
		}

		//we should load information of task
		$task_info=$this->task_manager_model->get_task_details($task_id);
		if(!$task_info)
			return;

		$this->data['task_info']=$task_info;
		$this->data['task_history']=$this->get_task_exec_history($customer_id,$task_id);

		$exec_info=$this->task_exec_manager_model->get_task_exec_info(array(
			"task_id"=>$task_id
			,"customer_id"=>$customer_id
		));
		if($exec_info)
			$this->data['task_exec_info']=$exec_info[0];
		else
			$this->data['task_exec_info']=NULL;
		
		$this->data['task_exec_statuses']=$this->task_exec_manager_model->get_task_statuses();

		return;
	}

	private function get_next_exec_date($days)
	{
		$date_function=DATE_FUNCTION;
		$next_seconds=time()+60*60*24*(int)$days;
		$next_exec=$date_function("Y-m-d H:i:s",$next_seconds);

		if($date_function !== "jdate")
			return $next_exec;

		list($next_date,$next_time)=explode(" ",$next_exec);
		list($year,$month,$day)=explode("-",$next_date);

		if($month == 2 && $day > 28)
			$day=28;

		if(($month == 4 || $month == 6) && ($day>30))
			$day=30;

		$next_exec=$year."-".$month."-".$day." ".$next_time;

		return $next_exec;
	}

	private function get_task_exec_history($customer_id,$task_id)
	{
		$types=array(
			"CUSTOMER_TASK_EXEC"
			,"CUSTOMER_TASK_MANAGER_NOTE"
		);
		
		$logs=$this->customer_manager_model->get_customer_logs($customer_id,array("log_types"=>$types));

		if(!$logs || !$logs['total'])
			return NULL;

		$logs=$logs['results'];

		$ret=array();

		for($i=sizeof($logs)-1;$i>=0;$i--)
		{
			$log=$logs[$i];
			
			if($log->task_id != $task_id)
				continue;

			if($log->log_type==="CUSTOMER_TASK_EXEC")
				$ret[]=$log;
			else
			{
				$ret_size=sizeof($ret);
				if($ret_size==0)
					continue;

				$ret[$ret_size-1]->manager_note[]=$log;
			}
		}

		return $ret;
	}


}
