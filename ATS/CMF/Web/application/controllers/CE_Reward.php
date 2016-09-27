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
}