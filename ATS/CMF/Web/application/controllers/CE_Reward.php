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
	}

	public function index()
	{	
		$this->customer_info=$this->customer_manager_model->get_logged_customer_info();
		if("teacher"===$this->customer_info['customer_type'])
			$this->teacher();
		else
			$this->student();
	}

	private function teacher()
	{
		$teacher_id=$this->customer_info['customer_id'];
		$classes=$this->class_manager_model->get_teacher_classes($teacher_id);
		bprint_r($classes);
	}
}