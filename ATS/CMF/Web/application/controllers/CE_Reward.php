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

	public function index($class_id=0)
	{	
		if(!$this->customer_manager_model->has_customer_logged_in())
			redirect(get_link("customer_login"));

		$this->customer_info=$this->customer_manager_model->get_logged_customer_info();			

		if("teacher"===$this->customer_info['customer_type'])
			$this->teacher($class_id);
		else
			$this->student();
	}

	private function teacher($class_id)
	{
		$teacher_id=$this->customer_info['customer_id'];
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
			redirect(get_customer_reward_teacher_class_link(''));
			return;
		}

		if($this->input->post("post_type")==="add_rewards")
			return $this->add_rewards($class_id,$teacher_id);

		$this->data['teacher_classes']=$classes;
		$this->data['classes_names']=$this->class_manager_model->get_classes_names();
		$this->data['students']=$this->class_manager_model->get_students($class_id);
		$this->data['rand']=get_random_word(5);
		
		$this->data['message']=get_message();
		$this->data['class_id']=$class_id;

		$this->data['page_link']=get_customer_reward_teacher_class_link($class_id);
		$this->data['lang_pages']=get_lang_pages(get_customer_reward_teacher_class_link($class_id,TRUE));

		$this->data['header_title']=$this->lang->line("rewards").$this->lang->line("header_separator").$this->data['header_title'];

		$this->send_customer_output("reward_teacher");	
	}

	private function add_rewards($class_id,$teacher_id)
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
				$reward=$rewards[$sid];
				$desc=$subject;
				if(isset($mds[$sid]))
					$desc.=" ".$mds[$sid];

				$rewards_array[]=array(
					"student_id"=>$sid
					,"description"=>$desc
					,"value"=>intval($reward)
				);
			}
		}

		$this->reward_manager_model->add_rewards($teacher_id,$rewards_array);

		set_message($this->lang->line("rewards_added_successfully"));

		return redirect(get_customer_reward_teacher_class_link($class_id));
	}
}