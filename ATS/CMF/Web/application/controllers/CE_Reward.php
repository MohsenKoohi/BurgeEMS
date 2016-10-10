<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Reward extends Burge_CMF_Controller {
	protected $hit_level=2;
	private $customer_info=NULL;

	function __construct()
	{
		parent::__construct();
		
		$this->load->model(array(
			"reward_manager_model"
			,"class_manager_model"
			,"customer_manager_model"
			)
		);

		$this->lang->load('ce_reward',$this->selected_lang);

	}

	public function student_list()
	{
		$info=$this->customer_manager_model->get_logged_customer_info();
		if(!$info)
			return redirect(get_link("customer_login"));

		if("student"!==$info['customer_type'])
			return redirect(get_link("customer_dashboard"));
		
		$this->data['rewards']=$this->reward_manager_model->get_student_rewards($info['customer_id']);
		$this->data['total_rewards']=$this->reward_manager_model->get_student_total_rewards($info['customer_id']);
		$this->data['message']=get_message();
		$this->data['page_link']=get_link("customer_reward_student");
		$this->data['lang_pages']=get_lang_pages(get_link("customer_reward_student",TRUE));

		$this->data['header_title']=$this->lang->line("rewards").$this->lang->line("header_separator").$this->data['header_title'];

		$this->send_customer_output("reward_student_list");	
	}

	public function teacher_submit($class_id)
	{
		if(!$this->customer_manager_model->has_customer_logged_in())
			return redirect(get_link("customer_login"));

		$customer_info=$this->customer_manager_model->get_logged_customer_info();			

		if("teacher" !== $customer_info['customer_type'])
			return redirect(get_link("customer_dashboard"));

		$teacher_id=$customer_info['customer_id'];
		$classes=$this->class_manager_model->get_teacher_classes($teacher_id);

		if(!$classes)
		{
			redirect(get_link("customer_dashboard"));
			return;
		}

		if(!$class_id)
			$class_id=$classes[0];

		if(!in_array($class_id,$classes))
		{
			redirect(get_customer_reward_teacher_submit_class_link(0));
			return;
		}

		if($this->input->post("post_type")==="add_rewards")
			return $this->add_rewards($teacher_id,$class_id,0);

		$this->data['teacher_classes']=$classes;
		$this->data['classes_names']=$this->class_manager_model->get_classes_names();
		$this->data['students']=$this->class_manager_model->get_students($class_id);
		$this->data['rand']=get_random_word(5);
		
		$this->data['message']=get_message();
		$this->data['class_id']=$class_id;

		$this->data['page_link']=get_customer_reward_teacher_submit_class_link($class_id);
		$this->data['lang_pages']=get_lang_pages(get_customer_reward_teacher_submit_class_link($class_id,TRUE));

		$this->data['header_title']=$this->lang->line("submit_reward").$this->lang->line("header_separator").$this->data['header_title'];

		$this->send_customer_output("reward_teacher_submit");	
	}

	public function teacher_prize($class_id)
	{
		if(!$this->customer_manager_model->has_customer_logged_in())
			return redirect(get_link("customer_login"));

		$customer_info=$this->customer_manager_model->get_logged_customer_info();			

		if("teacher" !== $customer_info['customer_type'])
			return redirect(get_link("customer_dashboard"));

		$teacher_id=$customer_info['customer_id'];

		if(!$this->reward_manager_model->is_prize_teacher($teacher_id))
			return redirect(get_link("customer_dashboard"));
		
		$classes=$this->class_manager_model->get_teacher_classes($teacher_id);

		if(!$classes)
		{
			redirect(get_link("customer_dashboard"));
			return;
		}

		if(!$class_id)
			$class_id=$classes[0];

		if(!in_array($class_id,$classes))
		{
			redirect(get_customer_reward_teacher_prize_class_link(0));
			return;
		}

		if($this->input->post("post_type")==="add_rewards")
			return $this->add_rewards($teacher_id,$class_id,1);

		$this->data['teacher_classes']=$classes;
		$this->data['classes_names']=$this->class_manager_model->get_classes_names();
		$this->data['students']=$this->reward_manager_model->get_class_students_with_total_rewards($class_id);
		$this->data['rand']=get_random_word(5);
		
		$this->data['message']=get_message();
		$this->data['class_id']=$class_id;

		$this->data['page_link']=get_customer_reward_teacher_prize_class_link($class_id);
		$this->data['lang_pages']=get_lang_pages(get_customer_reward_teacher_prize_class_link($class_id,TRUE));

		$this->data['header_title']=$this->lang->line("submit_prize").$this->lang->line("header_separator").$this->data['header_title'];

		if($this->input->get("print")===NULL)
			$this->send_customer_output("reward_teacher_prize");
		else
		{
			foreach($this->lang->language as $index => $val)
			$this->data[$index."_text"]=$val;

			$this->load->library('parser');
			$this->parser->parse($this->get_customer_view_file("reward_teacher_prize_print"),$this->data);
		}
	}

	private function add_rewards($teacher_id,$class_id,$is_prize)
	{
		$students=$this->class_manager_model->get_students($class_id);
		$rand=$this->input->post("rand");
		$subject=$this->input->post("subject-".$rand);
		$rewards=$this->input->post("reward-".$rand);
		$mds=$this->input->post("md-".$rand);

		$rewards_array=array();

		foreach($students as $st)
		{
			$sid=$st['customer_id'];
			if(isset($rewards[$sid]) && $rewards[$sid])
			{
				$reward=intval(persian_normalize_word($rewards[$sid]));
				if(!$reward)
					continue;

				if($is_prize && ($reward > 0))
					$reward=-$reward;

				$desc="";
				if(isset($mds[$sid]))
					$desc=$mds[$sid];

				$rewards_array[]=array(
					"student_id"=>$sid
					,"description"=>$desc
					,"value"=>$reward
				);
			}
		}

		$reward_id=$this->reward_manager_model->add_rewards($teacher_id,$class_id,$subject,$rewards_array,$is_prize);

		set_message($this->lang->line("rewards_added_successfully"));

		return redirect(get_customer_reward_teacher_list_class_link($class_id,$reward_id));
	}

	public function teacher_list($class_id=0,$reward_id=0)
	{
		if(!$this->customer_manager_model->has_customer_logged_in())
			return redirect(get_link("customer_login"));

		$customer_info=$this->customer_manager_model->get_logged_customer_info();			

		if("teacher" !== $customer_info['customer_type'])
			return redirect(get_link("customer_dashboard"));

		$teacher_id=$customer_info['customer_id'];
		$classes=$this->class_manager_model->get_teacher_classes($teacher_id);

		if(!$classes)
		{
			redirect(get_link("customer_dashboard"));
			return;
		}

		if(!$class_id)
			$class_id=$classes[0];

		if(!in_array($class_id,$classes))
		{
			redirect(get_customer_reward_teacher_list_class_link(0));
			return;
		}

		$this->data['teacher_classes']=$classes;
		$this->data['classes_names']=$this->class_manager_model->get_classes_names();

		if(!$reward_id)
			return $this->reward_teacher_class($teacher_id,$class_id);
		else
			return $this->reward_teacher_values($teacher_id,$class_id,$reward_id);
	}

	private function reward_teacher_class($teacher_id,$class_id)
	{
		$this->data['message']=get_message();
		$this->data['class_id']=$class_id;
		$this->data['rewards_list']=$this->reward_manager_model->get_rewards_list($teacher_id,$class_id);

		$this->data['page_link']=get_customer_reward_teacher_list_class_link($class_id,0);
		$this->data['lang_pages']=get_lang_pages(get_customer_reward_teacher_list_class_link($class_id,0,TRUE));

		$this->data['header_title']=$this->lang->line("rewards").$this->lang->line("header_separator").$this->data['header_title'];

		$this->send_customer_output("reward_teacher_class");	
	}

	private function reward_teacher_values($teacher_id,$class_id,$reward_id)
	{
		$reward_info=$this->reward_manager_model->get_reward_info($reward_id);
		
		if(
			!$reward_info 
			|| ($teacher_id != $reward_info['reward_teacher_id'])  
			|| ($class_id != $reward_info['reward_class_id'])
		)
			return redirect(get_link("customer_dashboard"));

		$this->data['reward_subject']=$reward_info['reward_subject'];
		$this->data['reward_date']=$reward_info['reward_date'];
		$this->data['reward_editable']=$reward_info['reward_editable'];

		$this->data['students_rewards']=$this->reward_manager_model->get_reward_values($reward_id);

		$this->data['message']=get_message();
		$this->data['page_link']=get_customer_reward_teacher_list_class_link($class_id,$reward_id);
		$this->data['edit_link']=get_customer_reward_teacher_edit_link($reward_id);
		$this->data['lang_pages']=get_lang_pages(get_customer_reward_teacher_list_class_link($class_id,$reward_id,TRUE));

		$this->data['header_title']=
			$reward_info['reward_subject'].$this->lang->line("header_separator")
			.$this->lang->line("rewards").$this->lang->line("header_separator")
			.$this->data['header_title'];

		$this->send_customer_output("reward_teacher_values");	
	}

	public function teacher_edit($reward_id)
	{
		$reward_id=(int)$reward_id;
		if(!$reward_id || !$this->customer_manager_model->has_customer_logged_in())
			return redirect(get_link("customer_login"));

		$customer_info=$this->customer_manager_model->get_logged_customer_info();			
		if("teacher" !== $customer_info['customer_type'])
			return redirect(get_link("customer_dashboard"));

		$teacher_id=$customer_info['customer_id'];
		$reward_info=$this->reward_manager_model->get_reward_info($reward_id);
		if(
				!$reward_info 
				|| !$reward_info['reward_editable'] 
				|| ($reward_info['reward_teacher_id'] != $teacher_id)
			)
			return redirect(get_link("customer_dashboard"));

		if($this->input->post("post_type")==="edit_rewards")
			return $this->modify_rewards($reward_info);

		$class_id=$reward_info['reward_class_id'];
		$teacher_id=$reward_info['reward_teacher_id'];
		$reward_id=$reward_info['reward_id'];

		$this->data['reward_subject']=$reward_info['reward_subject'];
		$this->data['reward_date']=$reward_info['reward_date'];
		$this->data['rand']=get_random_word(5);

		$this->data['students_rewards']=$this->reward_manager_model->get_reward_values($reward_id);
		$this->data['students']=$this->class_manager_model->get_students($class_id);

		$this->data['message']=get_message();
		$this->data['page_link']=get_customer_reward_teacher_edit_link($reward_id);
		$this->data['lang_pages']=get_lang_pages(get_customer_reward_teacher_edit_link($reward_id,TRUE));

		$this->data['header_title']=
			$reward_info['reward_subject'].$this->lang->line("header_separator")
			.$this->lang->line("rewards").$this->lang->line("header_separator")
			.$this->data['header_title'];

		$this->send_customer_output("reward_teacher_values_edit");		
	}

	private function modify_rewards($reward_info)
	{
		$class_id=$reward_info['reward_class_id'];
		$teacher_id=$reward_info['reward_teacher_id'];
		$reward_id=$reward_info['reward_id'];
		$is_prize=$reward_info['reward_is_prize'];

		$students=$this->class_manager_model->get_students($class_id);
		$rand=$this->input->post("rand");
		$subject=$this->input->post("subject-".$rand);
		$rewards=$this->input->post("reward-".$rand);
		$mds=$this->input->post("md-".$rand);

		$rewards_array=array();

		foreach($students as $st)
		{
			$sid=$st['customer_id'];
			if(isset($rewards[$sid]) && $rewards[$sid])
			{
				$reward=intval(persian_normalize_word($rewards[$sid]));
				if(!$reward)
					continue;

				if($is_prize && ($reward > 0))
					$reward=-$reward;

				$desc="";
				if(isset($mds[$sid]))
					$desc=$mds[$sid];

				$rewards_array[]=array(
					"student_id"=>$sid
					,"description"=>$desc
					,"value"=>$reward
				);
			}
		}

		$this->reward_manager_model->edit_rewards($reward_id,$subject,$rewards_array,$teacher_id);

		set_message($this->lang->line("rewards_editted_successfully"));

		return redirect(get_customer_reward_teacher_list_class_link($class_id,$reward_id));
	}

}